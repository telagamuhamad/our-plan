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

        $meetings = $meetings->with('user', 'travels')->paginate(10);

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

    /**
     * Get the next upcoming meeting
     */
    public function getNextMeeting()
    {
        return $this->model->with('user', 'travels')
            ->where('start_date', '>=', Carbon::now()->toDateString())
            ->orderBy('start_date', 'asc')
            ->first();
    }

    /**
     * Get countdown data for the next meeting
     */
    public function getCountdown()
    {
        $meeting = $this->getNextMeeting();

        if (!$meeting) {
            return [
                'meeting' => null,
                'countdown' => null,
                'has_upcoming' => false,
                'message' => 'Belum ada meeting yang dijadwalkan',
            ];
        }

        return [
            'meeting' => $meeting,
            'countdown' => $meeting->countdown,
            'formatted_countdown' => $meeting->formatted_countdown,
            'has_upcoming' => true,
            'message' => $meeting->countdown['message'],
        ];
    }
}