<?php

namespace App\Console\Commands;

use App\Models\DailyQuestionTemplate;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateDailyQuestion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-daily-question
                            {--date= : Date to generate question for (Y-m-d). Default is today.}
                            {--category= : Specific category to use. Random if not specified.}
                            {--days=1 : Number of days to generate ahead.}
                            {--force : Force regenerate even if question exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily question for couples using AI (Gemini)';

    /**
     * Execute the console command.
     */
    public function handle(GeminiService $gemini): int
    {
        $days = (int) $this->option('days', 1);
        $specificDate = $this->option('date');
        $specificCategory = $this->option('category');
        $force = $this->option('force');

        // Check if Gemini is configured
        if (!$gemini->isConfigured()) {
            $this->warn('Gemini API key is not configured. Using fallback static questions.');
            $this->warn('Set GEMINI_API_KEY in your .env file to use AI-generated questions.');
        }

        $generated = 0;
        $skipped = 0;
        $failed = 0;

        // Determine dates to generate
        $dates = [];
        if ($specificDate) {
            $dates[] = Carbon::parse($specificDate);
        } else {
            for ($i = 0; $i < $days; $i++) {
                $dates[] = Carbon::now()->addDays($i);
            }
        }

        $this->info("Generating questions for " . count($dates) . " date(s)...");
        $this->newLine();

        foreach ($dates as $date) {
            $dateStr = $date->toDateString();
            $this->line("Processing date: <info>{$date->format('l, d F Y')}</info>");

            // Check if already exists
            $existing = DailyQuestionTemplate::getByDate($dateStr);
            if ($existing && !$force) {
                $this->line("  - Question already exists. Use --force to regenerate.");
                $this->line("  - Current: <comment>{$existing->question}</comment>");
                $skipped++;
                continue;
            }

            // Delete existing if force
            if ($existing && $force) {
                $existing->delete();
                $this->line("  - Deleted existing question.");
            }

            try {
                // Generate question
                $category = $specificCategory ?: null;
                $result = $gemini->generateDailyQuestion($category, 'id');

                // Save to database
                DailyQuestionTemplate::create([
                    'question_date' => $dateStr,
                    'question' => $result['question'],
                    'category' => $result['category'],
                    'locale' => $result['locale'],
                    'ai_model' => $result['ai_model'],
                    'ai_response' => $result['ai_response'],
                    'is_fallback' => $result['is_fallback'],
                ]);

                $type = $result['is_fallback'] ? '<comment>Fallback</comment>' : '<info>AI Generated</info>';
                $this->line("  - Question generated: {$type}");
                $this->line("  - Category: <fg=cyan>{$result['category']}</>");
                $this->line("  - Question: <fg=yellow>{$result['question']}</fog>");
                $this->newLine();

                $generated++;
            } catch (\Exception $e) {
                $this->error("  - Failed to generate question: " . $e->getMessage());
                $failed++;
            }
        }

        // Summary
        $this->newLine();
        $this->info("=== Summary ===");
        $this->line("Generated: <info>{$generated}</info>");
        $this->line("Skipped: <comment>{$skipped}</comment>");
        $this->line("Failed: <error>{$failed}</error>");

        return $failed > 0 ? 1 : 0;
    }
}
