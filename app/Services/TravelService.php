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

    public function assignToMeeting($meeting, $travel, $visitDate)
    {
        // Validate visit date
        $this->validateVisitDate($visitDate, $meeting->start_date, $meeting->end_date);

        return $this->repository->assignToMeeting($meeting->id, $travel->id, $visitDate);
    }

    public function removeFromMeeting($travelId)
    {
        return $this->repository->removeFromMeeting($travelId);
    }

    public function completeTravel($travelId) {
        return $this->repository->completeTravel($travelId);
    }

    public function findWithoutMeeting()
    {
        return $this->repository->findWithoutMeeting();
    }

    private function validateVisitDate($visitDate, $meetingStartDate, $meetingEndDate) {
        if ($visitDate < $meetingStartDate || $visitDate > $meetingEndDate) {
            throw new Exception('Tanggal perjalanan harus dalam rentang tanggal meeting.');
        }

        return true;
    }
}