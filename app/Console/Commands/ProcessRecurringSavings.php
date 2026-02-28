<?php

namespace App\Console\Commands;

use App\Models\RecurringSaving;
use App\Services\RecurringSavingService;
use Illuminate\Console\Command;

class ProcessRecurringSavings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'savings:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process recurring savings deposits that are due';

    /**
     * Execute the console command.
     */
    public function handle(RecurringSavingService $recurringSavingService)
    {
        $this->info('Processing recurring savings...');

        // Get all due recurring savings
        $dueRecurrings = RecurringSaving::due()->with('savingModel', 'user')->get();

        if ($dueRecurrings->isEmpty()) {
            $this->info('No recurring savings due for processing.');
            return Command::SUCCESS;
        }

        $this->info("Found {$dueRecurrings->count()} recurring savings to process.");

        $processed = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($dueRecurrings as $recurring) {
            $name = $recurring->name ?: $recurring->savingModel->name;
            $this->line("Processing: {$name} (ID: {$recurring->id})");

            try {
                $result = $recurringSavingService->processDeposit($recurring);

                if ($result) {
                    $processed++;
                    $this->info("✓ Successfully processed recurring saving #{$recurring->id}");

                    // Optionally notify the user
                    // $recurring->user->notify(new RecurringDepositProcessed($recurring));
                } else {
                    $skipped++;
                    $this->comment("- Skipped recurring saving #{$recurring->id}");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Failed to process recurring saving #{$recurring->id}: {$e->getMessage()}");
                report($e);
            }
        }

        $this->newLine();
        $this->info('Recurring savings processing completed.');
        $this->table(
            ['Status', 'Count'],
            [
                ['Processed', $processed],
                ['Skipped', $skipped],
                ['Failed', $failed],
                ['Total', $dueRecurrings->count()],
            ]
        );

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
