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
        $travels = $this->model->with('meeting')->orWhereNull('meeting_id');

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
}