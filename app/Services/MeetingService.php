<?php

namespace App\Services;

use App\Repositories\MeetingRepository;
use Exception;
use Illuminate\Support\Facades\Auth;

class MeetingService {
    protected $repository;

    public function __construct(MeetingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllMeetings(array $searchTerms = [])
    {
        return $this->repository->getAllMeetings($searchTerms);
    }

    public function createMeeting(array $payload)
    {
        return $this->repository->createMeeting($payload);
    }

    public function findMeetingById($meetingId)
    {
        $meeting = $this->repository->findMeetingById($meetingId);
        if (empty($meeting)) {
            throw new Exception('Meeting not found.');
        }

        return $meeting;
    }

    public function updateMeeting($meetingId, array $data)
    {
        $meeting = $this->repository->findMeetingById($meetingId);

        $formattedUserId = (string) Auth::id();
        if ($meeting->travelling_user_id !== $formattedUserId) {
            throw new Exception('You are not authorized to update this meeting.');
        }

        return $this->repository->updateMeeting($meeting, $data);
    }

    public function deleteMeeting($meetingId)
    {
        $meeting = $this->repository->findMeetingById($meetingId);
        $formattedUserId = (string) Auth::id();
        if ($meeting->travelling_user_id !== $formattedUserId) {
            throw new Exception('You are not authorized to delete this meeting.');
        }

        return $this->repository->deleteMeeting($meeting);
    }
}