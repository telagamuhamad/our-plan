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

        $travels = $travels->paginate(10);

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
}