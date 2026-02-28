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

    /**
     * Get meeting analytics for a couple
     */
    public function getAnalytics($coupleId = null)
    {
        $now = Carbon::now();
        $startOfMonth = $now->startOfMonth()->toDateString();
        $startOfYear = $now->startOfYear()->toDateString();

        // Get all meetings
        $allMeetings = $this->model->with('feedbacks')->get();

        // Total meetings
        $totalMeetings = $allMeetings->count();

        // Completed meetings (end date has passed)
        $completedMeetings = $allMeetings->filter(function ($meeting) use ($now) {
            return Carbon::parse($meeting->end_date)->endOfDay()->lt($now);
        });

        // Upcoming meetings
        $upcomingMeetings = $allMeetings->filter(function ($meeting) use ($now) {
            return Carbon::parse($meeting->start_date)->startOfDay()->gte($now);
        });

        // In progress meetings
        $inProgressMeetings = $allMeetings->filter(function ($meeting) use ($now) {
            $start = Carbon::parse($meeting->start_date)->startOfDay();
            $end = Carbon::parse($meeting->end_date)->endOfDay();
            return $now->gte($start) && $now->lte($end);
        });

        // Average rating
        $feedbacks = $allMeetings->flatMap->feedbacks;
        $avgRating = $feedbacks->isNotEmpty() ? round($feedbacks->avg('rating'), 1) : 0;
        $totalFeedbacks = $feedbacks->count();

        // Most frequent location
        $locationCounts = $allMeetings->where('location', '!=', null)->countBy('location');
        $mostFrequentLocation = $locationCounts->sortDesc()->keys()->first();

        // Total days spent together (sum of all meeting durations)
        $totalDaysSpent = $completedMeetings->sum(function ($meeting) {
            $start = Carbon::parse($meeting->start_date);
            $end = Carbon::parse($meeting->end_date);
            return $start->diffInDays($end) + 1; // +1 to count both start and end day
        });

        // This month meetings
        $thisMonthMeetings = $allMeetings->filter(function ($meeting) use ($startOfMonth) {
            return Carbon::parse($meeting->start_date)->gte($startOfMonth);
        });

        // This year meetings
        $thisYearMeetings = $allMeetings->filter(function ($meeting) use ($startOfYear) {
            return Carbon::parse($meeting->start_date)->gte($startOfYear);
        });

        // Meetings by location (for chart/list)
        $meetingsByLocation = $allMeetings->where('location', '!=', null)
            ->groupBy('location')
            ->map(function ($meetings) {
                return [
                    'count' => $meetings->count(),
                    'last_visit' => $meetings->max('end_date'),
                ];
            })
            ->sortByDesc('count');

        return [
            'total_meetings' => $totalMeetings,
            'completed_meetings' => $completedMeetings->count(),
            'upcoming_meetings' => $upcomingMeetings->count(),
            'in_progress_meetings' => $inProgressMeetings->count(),
            'average_rating' => $avgRating,
            'total_feedbacks' => $totalFeedbacks,
            'most_frequent_location' => $mostFrequentLocation ?: '-',
            'total_days_spent' => $totalDaysSpent,
            'this_month_meetings' => $thisMonthMeetings->count(),
            'this_year_meetings' => $thisYearMeetings->count(),
            'meetings_by_location' => $meetingsByLocation,
            'recent_meetings' => $allMeetings->sortByDesc('start_date')->take(5)->values(),
            'next_meeting' => $this->getNextMeeting(),
        ];
    }
}