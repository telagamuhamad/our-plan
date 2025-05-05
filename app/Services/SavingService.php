<?php

namespace App\Services;

use App\Repositories\SavingRepository;
use App\Repositories\SavingTransactionRepository;
use Illuminate\Support\Facades\Auth;

class SavingService{
    protected $repository;
    protected $savingTransactionRepository;

    public function __construct(SavingRepository $repository, SavingTransactionRepository $savingTransactionRepository)
    {
        $this->repository = $repository;
        $this->savingTransactionRepository = $savingTransactionRepository;
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

    public function transfer($sourceSaving, $targetSaving, $amount)
    {
        $user = Auth::user();
        // reduce source saving amount
        $sourceSaving->decrement('current_amount', $amount);

        // increase target saving amount
        $targetSaving->increment('current_amount', $amount);

        $this->savingTransactionRepository->create([
            'saving_id' => $sourceSaving->id,
            'type' => 'transfer',
            'amount' => $amount,
            'note' => 'Transfer ke ' . $targetSaving->name,
            'actor_user_id' => $user->id
        ]);

        $this->savingTransactionRepository->create([
            'saving_id' => $targetSaving->id,
            'type' => 'transfer',
            'amount' => $amount,
            'note' => 'Transfer dari ' . $sourceSaving->name,
            'actor_user_id' => $user->id
        ]);
    }
}