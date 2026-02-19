<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\MeetingFeedbackService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeetingFeedbackApiController extends Controller
{
    protected $service;

    public function __construct(MeetingFeedbackService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all feedbacks for a meeting
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

            return response()->json([
                'success' => true,
                'message' => 'Feedback berhasil dikirim!',
                'data' => $feedback->load('user')
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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

            return response()->json([
                'success' => true,
                'message' => 'Feedback berhasil diupdate!',
                'data' => $feedback->load('user')
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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

            return response()->json([
                'success' => true,
                'message' => 'Feedback berhasil dihapus!'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Check if user can give feedback
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
