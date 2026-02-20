<?php

namespace App\Services;

use App\Models\RecurringSaving;
use App\Models\Saving;
use App\Models\SavingTransaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class RecurringSavingService
{
    public function getAllForUser(User $user)
    {
        return RecurringSaving::with('savingModel')->where('user_id', $user->id)->get();
    }

    public function getBySavingId($savingId)
    {
        return RecurringSaving::with('savingModel')->where('saving_id', $savingId)->get();
    }

    public function find($id)
    {
        return RecurringSaving::with('savingModel', 'user')->find($id);
    }

    public function create(array $payload, User $user)
    {
        $saving = Saving::find($payload['saving_id']);
        if (!$saving) {
            throw new Exception('Saving not found');
        }

        $startDate = Carbon::parse($payload['start_date']);
        $nextRunDate = match($payload['frequency']) {
            'daily' => $startDate->copy()->addDay(),
            'weekly' => $startDate->copy()->addWeek(),
            'biweekly' => $startDate->copy()->addWeeks(2),
            'monthly' => $startDate->copy()->addMonth(),
            default => $startDate->copy()->addMonth(),
        };

        return RecurringSaving::create([
            'saving_id' => $payload['saving_id'],
            'user_id' => $user->id,
            'frequency' => $payload['frequency'],
            'amount' => $payload['amount'],
            'name' => $payload['name'] ?? null,
            'start_date' => $startDate,
            'next_run_date' => $nextRunDate,
            'end_date' => $payload['end_date'] ?? null,
        ]);
    }

    public function update($id, array $payload)
    {
        $recurring = $this->find($id);
        if (!$recurring) {
            throw new Exception('Recurring saving not found');
        }

        // Recalculate next run date if frequency changed
        if (isset($payload['frequency']) && $payload['frequency'] !== $recurring->frequency) {
            $baseDate = $recurring->last_run_date ? Carbon::parse($recurring->last_run_date) : Carbon::parse($recurring->start_date);

            $payload['next_run_date'] = match($payload['frequency']) {
                'daily' => $baseDate->copy()->addDay(),
                'weekly' => $baseDate->copy()->addWeek(),
                'biweekly' => $baseDate->copy()->addWeeks(2),
                'monthly' => $baseDate->copy()->addMonth(),
                default => $baseDate->copy()->addMonth(),
            };
        }

        return $recurring->update($payload);
    }

    public function delete($id)
    {
        $recurring = $this->find($id);
        if (!$recurring) {
            return false;
        }

        return $recurring->delete();
    }

    public function pause($id)
    {
        $recurring = $this->find($id);
        if (!$recurring) {
            throw new Exception('Recurring saving not found');
        }

        return $recurring->pause();
    }

    public function resume($id)
    {
        $recurring = $this->find($id);
        if (!$recurring) {
            throw new Exception('Recurring saving not found');
        }

        return $recurring->resume();
    }

    public function skip($id)
    {
        $recurring = $this->find($id);
        if (!$recurring) {
            throw new Exception('Recurring saving not found');
        }

        return $recurring->skip();
    }

    public function processDeposit(RecurringSaving $recurring)
    {
        if (!$recurring->isDue) {
            return false;
        }

        DB::beginTransaction();

        try {
            $saving = $recurring->savingModel;

            SavingTransaction::create([
                'saving_id' => $saving->id,
                'type' => 'deposit',
                'amount' => $recurring->amount,
                'note' => 'Auto-deposit: ' . ($recurring->name ?? $recurring->formatted_frequency),
                'actor_user_id' => $recurring->user_id,
            ]);

            $saving->increment('current_amount', $recurring->amount);

            $recurring->markAsProcessed();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function processAllDue()
    {
        $dueRecurrings = RecurringSaving::due()->with('savingModel')->get();

        $processed = 0;
        $failed = 0;

        foreach ($dueRecurrings as $recurring) {
            try {
                $this->processDeposit($recurring);
                $processed++;
            } catch (Exception $e) {
                $failed++;
                report($e);
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'total' => $dueRecurrings->count(),
        ];
    }

    public function getActiveCountForUser(User $user)
    {
        return RecurringSaving::where('user_id', $user->id)->active()->count();
    }

    public function getDueCountForUser(User $user)
    {
        return RecurringSaving::where('user_id', $user->id)->due()->count();
    }

    public function getStatsForUser(User $user)
    {
        $recurrings = RecurringSaving::where('user_id', $user->id)->get();

        return [
            'total' => $recurrings->count(),
            'active' => $recurrings->where('is_active', true)->whereNull('paused_at')->count(),
            'paused' => $recurrings->whereNotNull('paused_at')->count(),
            'due' => $recurrings->filter(fn($r) => $r->is_due)->count(),
            'total_deposited' => $recurrings->sum('total_deposited_amount'),
            'monthly_total' => $recurrings->active()
                ->filter(fn($r) => $r->frequency === 'monthly')
                ->sum('amount'),
        ];
    }
}
