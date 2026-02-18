<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DailyQuestionResource;
use App\Models\DailyQuestion;
use App\Services\QuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    protected QuestionService $service;

    public function __construct(QuestionService $service)
    {
        $this->service = $service;
    }

    /**
     * Get today's question.
     */
    public function today(): JsonResponse
    {
        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki pasangan aktif',
            ], 403);
        }

        $question = $this->service->getTodayQuestion($couple);

        return response()->json([
            'success' => true,
            'data' => DailyQuestionResource::make($question),
        ]);
    }

    /**
     * Submit answer to today's question.
     */
    public function answer(Request $request): JsonResponse
    {
        $request->validate([
            'answer' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki pasangan aktif',
            ], 403);
        }

        try {
            $question = $this->service->getTodayQuestion($couple);

            $question = $this->service->submitAnswer($question, $user, $request->answer);

            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan!',
                'data' => DailyQuestionResource::make($question),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update existing answer.
     */
    public function updateAnswer(Request $request): JsonResponse
    {
        $request->validate([
            'question_id' => 'required|integer',
            'answer' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        try {
            $question = DailyQuestion::findOrFail($request->question_id);

            if ($question->couple_id !== $user->couple_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $question = $this->service->updateAnswer($question, $user, $request->answer);

            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil diupdate!',
                'data' => DailyQuestionResource::make($question),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get question history.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki pasangan aktif',
            ], 403);
        }

        $limit = (int) $request->get('limit', 30);
        $history = $this->service->getHistory($couple, $limit);

        return response()->json([
            'success' => true,
            'data' => DailyQuestionResource::collection($history),
        ]);
    }

    /**
     * Get question stats.
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki pasangan aktif',
            ], 403);
        }

        $stats = $this->service->getStats($couple);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get question by date.
     */
    public function show(Request $request, string $date): JsonResponse
    {
        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki pasangan aktif',
            ], 403);
        }

        $question = $this->service->getQuestionByDate($couple, $date);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Pertanyaan tidak ditemukan untuk tanggal tersebut',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => DailyQuestionResource::make($question),
        ]);
    }

    /**
     * Get available categories.
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => DailyQuestion::getCategories(),
            ],
        ]);
    }

    /**
     * Set answer mode preference.
     */
    public function setAnswerMode(Request $request): JsonResponse
    {
        $request->validate([
            'question_id' => 'required|integer',
            'mode' => 'required|in:app,call',
        ]);

        $user = Auth::user();

        try {
            $question = DailyQuestion::findOrFail($request->question_id);

            if ($question->couple_id !== $user->couple_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $question = $this->service->setAnswerMode($question, $user, $request->mode);

            return response()->json([
                'success' => true,
                'message' => 'Mode jawab berhasil diubah!',
                'data' => DailyQuestionResource::make($question),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get available answer modes.
     */
    public function answerModes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'modes' => DailyQuestion::getAnswerModes(),
            ],
        ]);
    }
}
