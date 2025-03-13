<?php

namespace App\Repositories;

use App\Models\Meeting;
use Carbon\Carbon;

class MeetingRepository {
    protected $model;

    public function __construct(Meeting $model)
    {
        $this->model = $model;
    }

    public function getAllMeetings(array $searchTerms = [])
    {
        $meetings = $this->model;

        if (!empty($searchTerms)) {
            if (!empty($searchTerms['traveler_name'])) {
                $meetings = $meetings->whereHas('user', function ($query) use ($searchTerms) {
                    $query->where('name', 'like', '%' . $searchTerms['traveler_name'] . '%');
                });
            }

            if (!empty($searchTerms['location'])) {
                $meetings = $meetings->where('location', 'like', '%' . $searchTerms['location'] . '%');
            }

            if (!empty($searchTerms['meeting_date'])) {
                $dateNow = Carbon::now()->format('Y-m-d');
                $meetings = $meetings->whereBetween('meeting_date', [$searchTerms['meeting_date'], $dateNow]);
            }
        }

        $meetings = $meetings->with('user')->paginate(10);

        return $meetings;
    }

    public function createMeeting(array $payload)
    {
        return $this->model->create($payload);
    }

    public function findMeetingById($meetingId)
    {
        return $this->model->find($meetingId);
    }

    public function updateMeeting(Meeting $meeting, array $data)
    {
        return $meeting->update($data);
    }

    public function deleteMeeting(Meeting $meeting)
    {
        if (empty($meeting)) {
            return false;
        }

        return $meeting->delete();
    }
}