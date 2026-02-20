<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\Notification;
use App\Models\Saving;
use App\Models\User;
use App\Repositories\SavingRepository;
use App\Repositories\SavingTransactionRepository;
use Illuminate\Support\Facades\Auth;

class SavingService
{
    protected $repository;
    protected $savingTransactionRepository;

    public function __construct(SavingRepository $repository, SavingTransactionRepository $savingTransactionRepository)
    {
        $this->repository = $repository;
        $this->savingTransactionRepository = $savingTransactionRepository;
    }

    public function getAllSavings()
    {
        return $this->repository->getAllSavings();
    }

    public function findSaving($id)
    {
        return $this->repository->find($id);
    }

    public function createSaving(array $payload)
    {
        return $this->repository->create($payload);
    }

    public function updateSaving($id, array $payload)
    {
        return $this->repository->update($id, $payload);
    }

    public function deleteSaving($id)
    {
        return $this->repository->delete($id);
    }

    public function transfer($sourceSaving, $targetSaving, $amount)
    {
        $user = Auth::user();
        // reduce source saving amount
        $sourceSaving->decrement('current_amount', $amount);

        // increase target saving amount
        $targetSaving->increment('current_amount', $amount);

        $this->savingTransactionRepository->create([
            'saving_id' => $sourceSaving->id,
            'type' => 'transfer',
            'amount' => $amount,
            'note' => 'Transfer ke ' . $targetSaving->name,
            'actor_user_id' => $user->id
        ]);

        $this->savingTransactionRepository->create([
            'saving_id' => $targetSaving->id,
            'type' => 'transfer',
            'amount' => $amount,
            'note' => 'Transfer dari ' . $sourceSaving->name,
            'actor_user_id' => $user->id
        ]);
    }

    /**
     * Check for milestone and create notification if reached
     */
    public function checkMilestone(Saving $saving, User $actor)
    {
        if (!$saving->shouldNotifyMilestone()) {
            return null;
        }

        $milestone = $saving->getNextMilestone();
        if ($milestone === null) {
            return null;
        }

        $couple = $actor->couple;
        if (!$couple || !$couple->isActive()) {
            return null;
        }

        // Notify partner
        $partner = $couple->getPartner($actor);
        if ($partner) {
            $this->notifyMilestone($saving, $milestone, $actor, $partner, $couple);
        }

        // Also notify the actor (for celebration)
        if ($milestone === 100) {
            $this->notifyMilestone($saving, $milestone, $actor, $actor, $couple);
        }

        // Update last notified milestone
        $saving->updateLastNotifiedMilestone($milestone);

        return $milestone;
    }

    /**
     * Create milestone notification
     */
    protected function notifyMilestone(Saving $saving, int $milestone, User $actor, User $recipient, Couple $couple)
    {
        $emoji = match($milestone) {
            25 => '🌱',
            50 => '📈',
            75 => '🔥',
            100 => '🎉',
            default => '💰'
        };

        $message = match($milestone) {
            25 => "{$saving->name} sudah mencapai 25%! {$emoji} Tetap semangat!",
            50 => "{$saving->name} sudah mencapai 50%! {$emoji} Setengah jalan!",
            75 => "{$saving->name} sudah mencapai 75%! {$emoji} Tinggal sedikit lagi!",
            100 => "{$saving->name} sudah tercapai 100%! {$emoji} Selamat! Target tercapai!",
            default => "{$saving->name} sudah mencapai {$milestone}%! {$emoji}"
        };

        Notification::create([
            'user_id' => $recipient->id,
            'couple_id' => $couple->id,
            'type' => "saving_milestone_{$milestone}",
            'actor_id' => $actor->id,
            'message' => $message,
            'link' => route('savings.show', $saving->id),
            'is_read' => false,
        ]);
    }

    /**
     * Mark saving as completed
     */
    public function markAsCompleted(Saving $saving, User $actor)
    {
        if ($saving->is_completed) {
            return false;
        }

        $saving->update(['completed_at' => now()]);

        // Notify couple about completion
        $couple = $actor->couple;
        if ($couple && $couple->isActive()) {
            $partner = $couple->getPartner($actor);
            if ($partner) {
                $this->notifyMilestone($saving, 100, $actor, $partner, $couple);
            }
        }

        return true;
    }

    /**
     * Get savings with upcoming deadlines
     */
    public function getUpcomingDeadlines(int $days = 7)
    {
        return Saving::upcoming($days)->get();
    }

    /**
     * Get overdue savings
     */
    public function getOverdueSavings()
    {
        return Saving::overdue()->get();
    }

    /**
     * Check and notify about overdue savings
     */
    public function checkAndNotifyOverdue(User $user)
    {
        $overdueSavings = $this->getOverdueSavings();

        foreach ($overdueSavings as $saving) {
            // Check if we already notified about this overdue saving
            $existingNotification = Notification::where('type', 'saving_overdue')
                ->where('user_id', $user->id)
                ->where('link', route('savings.show', $saving->id))
                ->where('created_at', '>=', now()->subDay())
                ->first();

            if (!$existingNotification) {
                $this->notifyOverdue($saving, $user);
            }
        }
    }

    /**
     * Create overdue notification
     */
    protected function notifyOverdue(Saving $saving, User $recipient)
    {
        $couple = $recipient->couple;
        if (!$couple) {
            return;
        }

        $remainingAmount = $saving->target_amount - $saving->current_amount;
        $message = "Target {$saving->name} telah terlewati. Masih kurang Rp " . number_format($remainingAmount, 0, ',', '.') . " untuk mencapai target.";

        Notification::create([
            'user_id' => $recipient->id,
            'couple_id' => $couple->id,
            'type' => 'saving_overdue',
            'actor_id' => $recipient->id,
            'message' => $message,
            'link' => route('savings.show', $saving->id),
            'is_read' => false,
        ]);
    }
}
