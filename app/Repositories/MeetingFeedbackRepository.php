<?php

namespace App\Repositories;

use App\Models\MeetingFeedback;

class MeetingFeedbackRepository
{
    protected $model;

    public function __construct(MeetingFeedback $model)
    {
        $this->model = $model;
    }

    /**
     * Get all feedbacks for a meeting
     */
    public function getByMeeting($meetingId)
    {
        return $this->model->with('user')
            ->byMeeting($meetingId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find feedback by meeting and user
     */
    public function findByMeetingAndUser($meetingId, $userId)
    {
        return $this->model->byMeeting($meetingId)
            ->byUser($userId)
            ->first();
    }

    /**
     * Create new feedback
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update feedback
     */
    public function update(MeetingFeedback $feedback, array $data)
    {
        return $feedback->update($data);
    }

    /**
     * Delete feedback
     */
    public function delete(MeetingFeedback $feedback)
    {
        return $feedback->delete();
    }

    /**
     * Get average rating for a meeting
     */
    public function getAverageRating($meetingId)
    {
        return $this->model->byMeeting($meetingId)->avg('rating') ?? 0;
    }

    /**
     * Check if user has given feedback for a meeting
     */
    public function hasUserGivenFeedback($meetingId, $userId)
    {
        return $this->model->byMeeting($meetingId)
            ->byUser($userId)
            ->exists();
    }

    /**
     * Find feedback by ID
     */
    public function find($id)
    {
        return $this->model->find($id);
    }
}
