<?php

namespace App\Http\Controllers;

use App\Models\DailyQuestion;
use App\Services\QuestionService;
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
     * Display today's question page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum memiliki pasangan aktif');
        }

        $todayQuestion = $this->service->getTodayQuestion($couple);
        $history = $this->service->getHistory($couple, 10);
        $stats = $this->service->getStats($couple);

        return view('questions.index', compact(
            'couple',
            'todayQuestion',
            'history',
            'stats'
        ));
    }

    /**
     * Submit answer to today's question.
     */
    public function answer(Request $request)
    {
        $request->validate([
            'answer' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $couple = $user?->couple;

        if (!$couple || !$couple->isActive()) {
            return back()->with('error', 'Anda belum memiliki pasangan aktif');
        }

        try {
            $question = $this->service->getTodayQuestion($couple);
            $this->service->submitAnswer($question, $user, $request->answer);

            return back()->with('success', 'Jawaban berhasil disimpan! 🎉');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan jawaban: ' . $e->getMessage());
        }
    }

    /**
     * Update existing answer.
     */
    public function update(Request $request)
    {
        $request->validate([
            'question_id' => 'required|integer',
            'answer' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        try {
            $question = DailyQuestion::findOrFail($request->question_id);

            if ($question->couple_id !== $user->couple_id) {
                return back()->with('error', 'Unauthorized');
            }

            $this->service->updateAnswer($question, $user, $request->answer);

            return back()->with('success', 'Jawaban berhasil diupdate! ✨');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengupdate jawaban: ' . $e->getMessage());
        }
    }

    /**
     * Get question by date (AJAX).
     */
    public function show(Request $request, string $date)
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
                'message' => 'Pertanyaan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $question,
        ]);
    }
}
