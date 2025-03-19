<?php

namespace App\Services;

use App\Repositories\SavingRepository;

class SavingService{
    protected $repository;

    public function __construct(SavingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllSavings()
    {
        return $this->repository->getAllSavings();
    }

    public function findSaving($id)
    {
        return $this->repository->find($id);
    }

    public function createSaving(array $payload)
    {
        return $this->repository->create($payload);
    }

    public function updateSaving($id, array $payload)
    {
        return $this->repository->update($id, $payload);
    }

    public function deleteSaving($id)
    {
        return $this->repository->delete($id);
    }
}