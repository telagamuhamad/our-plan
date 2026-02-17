<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\DailyQuestion;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class QuestionService
{
    /**
     * Get or create today's question for a couple.
     */
    public function getTodayQuestion(Couple $couple): DailyQuestion
    {
        $today = now()->toDateString();

        $question = DailyQuestion::where('couple_id', $couple->id)
            ->where('question_date', $today)
            ->first();

        if (!$question) {
            // Generate new question for today
            $random = DailyQuestion::getRandomQuestion();

            $question = DailyQuestion::create([
                'couple_id' => $couple->id,
                'question_date' => $today,
                'question' => $random['question'],
                'category' => $random['category'],
            ]);
        }

        return $question;
    }

    /**
     * Submit answer for a user.
     */
    public function submitAnswer(DailyQuestion $question, User $user, string $answer): DailyQuestion
    {
        if ($question->couple_id !== $user->couple_id) {
            throw new Exception('Unauthorized access to question');
        }

        if ($question->hasUserAnswered($user)) {
            throw new Exception('Kamu sudah menjawab pertanyaan ini');
        }

        DB::beginTransaction();
        try {
            $couple = $user->couple;

            if ($couple->isUserOne($user)) {
                $question->update([
                    'answer_one' => $answer,
                    'answered_by_one' => $user->id,
                    'answered_one_at' => now(),
                ]);
            } elseif ($couple->isUserTwo($user)) {
                $question->update([
                    'answer_two' => $answer,
                    'answered_by_two' => $user->id,
                    'answered_two_at' => now(),
                ]);
            } else {
                throw new Exception('User not part of this couple');
            }

            DB::commit();
            return $question->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update existing answer.
     */
    public function updateAnswer(DailyQuestion $question, User $user, string $answer): DailyQuestion
    {
        if ($question->couple_id !== $user->couple_id) {
            throw new Exception('Unauthorized access to question');
        }

        if (!$question->hasUserAnswered($user)) {
            throw new Exception('Belum ada jawaban untuk diupdate');
        }

        $couple = $user->couple;

        if ($couple->isUserOne($user)) {
            $question->update([
                'answer_one' => $answer,
                'answered_one_at' => now(),
            ]);
        } elseif ($couple->isUserTwo($user)) {
            $question->update([
                'answer_two' => $answer,
                'answered_two_at' => now(),
            ]);
        }

        return $question->fresh();
    }

    /**
     * Get question history for a couple.
     */
    public function getHistory(Couple $couple, int $limit = 30): \Illuminate\Database\Eloquent\Collection
    {
        return DailyQuestion::where('couple_id', $couple->id)
            ->with('answererOne', 'answererTwo')
            ->orderBy('question_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get question by date for a couple.
     */
    public function getQuestionByDate(Couple $couple, string $date): ?DailyQuestion
    {
        return DailyQuestion::where('couple_id', $couple->id)
            ->where('question_date', $date)
            ->first();
    }

    /**
     * Get stats for a couple.
     */
    public function getStats(Couple $couple): array
    {
        $total = DailyQuestion::where('couple_id', $couple->id)->count();
        $bothAnswered = DailyQuestion::where('couple_id', $couple->id)
            ->whereNotNull('answer_one')
            ->whereNotNull('answer_two')
            ->count();

        $categories = DailyQuestion::where('couple_id', $couple->id)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        return [
            'total_questions' => $total,
            'both_answered' => $bothAnswered,
            'completion_rate' => $total > 0 ? round(($bothAnswered / $total) * 100) : 0,
            'categories' => $categories,
        ];
    }

    /**
     * Check if user has answered today's question.
     */
    public function hasAnsweredToday(User $user): bool
    {
        $couple = $user->couple;
        if (!$couple || !$couple->isActive()) {
            return false;
        }

        $today = now()->toDateString();
        $question = DailyQuestion::where('couple_id', $couple->id)
            ->where('question_date', $today)
            ->first();

        if (!$question) {
            return false;
        }

        return $question->hasUserAnswered($user);
    }
}
