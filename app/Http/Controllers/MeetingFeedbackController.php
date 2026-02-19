<?php

namespace App\Http\Controllers;

use App\Services\MeetingFeedbackService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeetingFeedbackController extends Controller
{
    protected $service;

    public function __construct(MeetingFeedbackService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all feedbacks for a meeting (AJAX)
     */
    public function index($meetingId)
    {
        $data = $this->service->getByMeeting($meetingId);

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * Submit feedback for a meeting
     */
    public function store(Request $request, $meetingId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $feedback = $this->service->submitFeedback($meetingId, [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Feedback berhasil dikirim!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update feedback
     */
    public function update(Request $request, $feedbackId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $feedback = $this->service->updateFeedback($feedbackId, [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Feedback berhasil diupdate!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete feedback
     */
    public function destroy($feedbackId)
    {
        try {
            DB::beginTransaction();

            $this->service->deleteFeedback($feedbackId);

            DB::commit();

            return redirect()->back()->with('success', 'Feedback berhasil dihapus!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Check if user can give feedback (AJAX)
     */
    public function canGiveFeedback($meetingId)
    {
        $canGive = $this->service->canGiveFeedback($meetingId);

        return response()->json([
            'success' => true,
            'data' => [
                'can_give_feedback' => $canGive,
            ]
        ], 200);
    }
}
