<?php

namespace App\Services;

use App\Repositories\MeetingFeedbackRepository;
use App\Repositories\MeetingRepository;
use Exception;
use Illuminate\Support\Facades\Auth;

class MeetingFeedbackService
{
    protected $repository;
    protected $meetingRepository;

    public function __construct(
        MeetingFeedbackRepository $repository,
        MeetingRepository $meetingRepository
    ) {
        $this->repository = $repository;
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * Get all feedbacks for a meeting
     */
    public function getByMeeting($meetingId)
    {
        $meeting = $this->meetingRepository->findMeetingById($meetingId);

        $feedbacks = $this->repository->getByMeeting($meetingId);
        $averageRating = $this->repository->getAverageRating($meetingId);

        return [
            'meeting' => $meeting,
            'feedbacks' => $feedbacks,
            'average_rating' => round($averageRating, 1),
            'total_feedbacks' => $feedbacks->count(),
        ];
    }

    /**
     * Submit feedback for a meeting
     */
    public function submitFeedback($meetingId, array $data)
    {
        $meeting = $this->meetingRepository->findMeetingById($meetingId);

        // Check if meeting has ended (can only feedback after meeting)
        $endDate = \Carbon\Carbon::parse($meeting->end_date)->endOfDay();
        if (\Carbon\Carbon::now()->lt($endDate)) {
            throw new Exception('Belum bisa memberikan feedback. Meeting belum selesai.');
        }

        $userId = Auth::id();

        // Check if user already gave feedback
        $existing = $this->repository->findByMeetingAndUser($meetingId, $userId);
        if ($existing) {
            throw new Exception('Anda sudah memberikan feedback untuk meeting ini.');
        }

        $payload = [
            'meeting_id' => $meetingId,
            'user_id' => $userId,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ];

        return $this->repository->create($payload);
    }

    /**
     * Update feedback
     */
    public function updateFeedback($feedbackId, array $data)
    {
        $feedback = $this->repository->find($feedbackId);

        if (!$feedback) {
            throw new Exception('Feedback tidak ditemukan.');
        }

        if ($feedback->user_id !== Auth::id()) {
            throw new Exception('Anda tidak berhak mengupdate feedback ini.');
        }

        return $this->repository->update($feedback, $data);
    }

    /**
     * Delete feedback
     */
    public function deleteFeedback($feedbackId)
    {
        $feedback = $this->repository->find($feedbackId);

        if (!$feedback) {
            throw new Exception('Feedback tidak ditemukan.');
        }

        if ($feedback->user_id !== Auth::id()) {
            throw new Exception('Anda tidak berhak menghapus feedback ini.');
        }

        return $this->repository->delete($feedback);
    }

    /**
     * Check if user can give feedback for a meeting
     */
    public function canGiveFeedback($meetingId)
    {
        $meeting = $this->meetingRepository->findMeetingById($meetingId);
        $userId = Auth::id();

        // Check if meeting has ended
        $endDate = \Carbon\Carbon::parse($meeting->end_date)->endOfDay();
        $hasEnded = \Carbon\Carbon::now()->gte($endDate);

        // Check if user already gave feedback
        $hasGiven = $this->repository->hasUserGivenFeedback($meetingId, $userId);

        return $hasEnded && !$hasGiven;
    }
}
