<?php

namespace App\Repositories;

use App\Models\Travel;

class TravelRepository {
    protected $model;

    public function __construct(Travel $model)
    {
        $this->model = $model;
    }

    public function getAllTravels(array $searchTerms = [])
    {
        $travels = $this->model->orderBy('destination', 'asc');

        if (!empty($searchTerms)) {
            if (!empty($searchTerms['destination'])) {
                $travels = $travels->where('destination', 'like', '%' . $searchTerms['destination'] . '%');
            }

            if (!empty($searchTerms['visit_date'])) {
                $travels = $travels->where('visit_date', $searchTerms['visit_date']);
            }

            if (!empty($searchTerms['completed'])) {
                if ($searchTerms['completed'] === 'Completed') {
                    $travels = $travels->where('completed', true);
                } else {
                    $travels = $travels->where('completed', false);
                }
            }
        }

        $travels = $travels->with('meeting')->paginate(10);

        return $travels;
    }

    public function findTravelById($travelId)
    {
        return $this->model->find($travelId);
    }

    public function create(array $payload)
    {
        return $this->model->create($payload);
    }

    public function update(Travel $travel, array $data)
    {
        return $travel->update($data);
    }

    public function delete(Travel $travel)
    {
        return $travel->delete();
    }

    public function assignToMeeting($meetingId, $travelId,$visitDate)
    {
        return $this->model->where('id', $travelId)->update([
            'meeting_id' => $meetingId,
            'visit_date' => $visitDate
        ]);
    }

    public function removeFromMeeting($travelId)
    {
        return $this->model->where('id', $travelId)->update([
            'meeting_id' => null,
            'visit_date' => null
        ]);
    }

    public function completeTravel($travelId)
    {
        return $this->model->where('id', $travelId)->update([
            'completed' => true
        ]);
    }

    public function findWithoutMeeting()
    {
        return $this->model->whereNull('meeting_id')->get();
    }

    public function findWithMeeting($meetingId)
    {
        return $this->model->where('meeting_id', $meetingId)->get();
    }

    public function updateVisitDate($travelId, $visitDate)
    {
        return $this->model->where('id', $travelId)->update([
            'visit_date' => $visitDate
        ]);
    }

    /**
     * Get travel analytics
     */
    public function getAnalytics()
    {
        $allTravels = $this->model->with(['photos', 'journals', 'meeting'])->get();

        // Total travels
        $totalTravels = $allTravels->count();

        // Completed travels
        $completedTravels = $allTravels->where('completed', true)->count();
        $pendingTravels = $allTravels->where('completed', false)->count();

        // Travels with meeting
        $travelsWithMeeting = $allTravels->whereNotNull('meeting_id')->count();
        $travelsWithoutMeeting = $allTravels->whereNull('meeting_id')->count();

        // Total photos
        $totalPhotos = $allTravels->sum(function ($travel) {
            return $travel->photos->count();
        });

        // Total journals
        $totalJournals = $allTravels->sum(function ($travel) {
            return $travel->journals->count();
        });

        // Favorite journals count
        $favoriteJournals = $allTravels->sum(function ($travel) {
            return $travel->journals->where('is_favorite', true)->count();
        });

        // Most visited destinations (by travel count or can be from journals)
        $destinationCounts = $allTravels->whereNotNull('destination')
            ->countBy('destination')
            ->sortDesc()
            ->take(5);

        // Recent completed travels
        $recentCompleted = $allTravels->where('completed', true)
            ->sortByDesc('visit_date')
            ->take(5)
            ->values();

        // Upcoming travels
        $upcomingTravels = $allTravels->where('completed', false)
            ->whereNotNull('visit_date')
            ->sortBy('visit_date')
            ->take(5)
            ->values();

        // Travels per month/year stats
        $travelsByMonth = [];
        foreach ($allTravels as $travel) {
            if ($travel->visit_date) {
                $date = \Carbon\Carbon::parse($travel->visit_date);
                $key = $date->format('Y-m');
                if (!isset($travelsByMonth[$key])) {
                    $travelsByMonth[$key] = 0;
                }
                $travelsByMonth[$key]++;
            }
        }
        ksort($travelsByMonth);

        return [
            'total_travels' => $totalTravels,
            'completed_travels' => $completedTravels,
            'pending_travels' => $pendingTravels,
            'completion_rate' => $totalTravels > 0 ? round(($completedTravels / $totalTravels) * 100, 1) : 0,
            'travels_with_meeting' => $travelsWithMeeting,
            'travels_without_meeting' => $travelsWithoutMeeting,
            'total_photos' => $totalPhotos,
            'total_journals' => $totalJournals,
            'favorite_journals' => $favoriteJournals,
            'most_visited_destinations' => $destinationCounts,
            'recent_completed' => $recentCompleted,
            'upcoming_travels' => $upcomingTravels,
            'travels_by_month' => $travelsByMonth,
        ];
    }
}