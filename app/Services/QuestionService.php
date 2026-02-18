<?php

namespace App\Services;

use App\Models\Couple;
use App\Models\DailyQuestion;
use App\Models\DailyQuestionTemplate;
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
            // Get question from daily template (AI-generated or fallback)
            $template = DailyQuestionTemplate::getOrCreateForDate($today);

            $question = DailyQuestion::create([
                'couple_id' => $couple->id,
                'question_date' => $today,
                'question' => $template->question,
                'category' => $template->category,
            ]);
        }

        return $question;
    }

    /**
     * Set answer mode preference for a user.
     */
    public function setAnswerMode(DailyQuestion $question, User $user, string $mode): DailyQuestion
    {
        if ($question->couple_id !== $user->couple_id) {
            throw new Exception('Unauthorized access to question');
        }

        if (!in_array($mode, [DailyQuestion::ANSWER_MODE_APP, DailyQuestion::ANSWER_MODE_CALL])) {
            throw new Exception('Invalid answer mode');
        }

        $success = $question->setAnswerModeForUser($user, $mode);

        if (!$success) {
            throw new Exception('Failed to set answer mode');
        }

        return $question->fresh();
    }

    /**
     * Submit answer for a user.
     */
    public function submitAnswer(DailyQuestion $question, User $user, string $answer): DailyQuestion
    {
        if ($question->couple_id !== $user->couple_id) {
            throw new Exception('Unauthorized access to question');
        }

        // Check if user has set answer mode to call
        if ($question->doesUserPreferCall($user)) {
            throw new Exception('Kamu memilih untuk menjawab saat call. Ubah mode dulu jika ingin jawab sekarang.');
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

        // Check if user has set answer mode to call
        if ($question->doesUserPreferCall($user)) {
            throw new Exception('Kamu memilih untuk menjawab saat call. Ubah mode dulu jika ingin jawab sekarang.');
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

    /**
     * Get question data for API response.
     */
    public function getQuestionDataForApi(DailyQuestion $question, User $user): array
    {
        $couple = $user->couple;
        if (!$couple) {
            throw new Exception('User not part of a couple');
        }

        return [
            'id' => $question->id,
            'question' => $question->question,
            'category' => $question->category,
            'question_date' => $question->question_date->toDateString(),
            'formatted_date' => $question->formatted_date,
            'is_today' => $question->isToday(),
            'my_answer' => $question->getAnswerForUser($user),
            'my_answered_at' => $question->getAnsweredAtForUser($user)?->toIso8601String(),
            'my_answer_mode' => $question->getAnswerModeForUser($user),
            'can_answer_via_app' => $question->canUserAnswerViaApp($user),
            'prefers_call' => $question->doesUserPreferCall($user),
            // Partner info (without revealing answer if not both answered)
            'partner_answered' => $couple->isUserOne($user)
                ? ($question->answer_two !== null)
                : ($question->answer_one !== null),
            'both_answered' => $question->bothAnswered(),
            'both_prefer_call' => $question->bothPreferCall(),
        ];
    }
}
