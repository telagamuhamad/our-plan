<?php

namespace App\Services;

use App\Repositories\TravelRepository;
use Exception;

class TravelService {
    protected $repository;

    public function __construct(TravelRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllTravels(array $searchTerms = []) {
        return $this->repository->getAllTravels($searchTerms);
    }

    public function createTravel(array $payload) {
        return $this->repository->create($payload);
    }

    public function findTravelById($travelId) {
        $travel = $this->repository->findTravelById($travelId);
        if (empty($travel)) {
            throw new Exception('Travel not found.');
        }

        return $travel;
    }

    public function update($travelId, array $data) {
        $travel = $this->findTravelById($travelId);
        
        return $this->repository->update($travel, $data);
    }

    public function delete($travelId) {
        $travel = $this->findTravelById($travelId);

        return $this->repository->delete($travel);
    }
}