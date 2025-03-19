<?php

namespace App\Services;

use App\Repositories\SavingRepository;
use App\Repositories\SavingTransactionRepository;
use Exception;

class SavingTransactionService{
    protected $repository;
    protected $savingRepository;

    public function __construct(SavingTransactionRepository $repository, SavingRepository $savingRepository)
    {
        $this->repository = $repository;
        $this->savingRepository = $savingRepository;
    }

    public function getTransactionsBySavingId($savingId)
    {
        return $this->repository->getBySavingId($savingId);
    }

    public function addTransaction($savingId, array $payload)
    {
        $saving = $this->savingRepository->find($savingId);

        if ($payload['type'] === 'withdrawal' && $saving->current_amount < $payload['amount']) {
            throw new Exception('Saldo tidak cukup untuk melakukan penarikan.');
        }

        $this->repository->create([
            'saving_id' => $savingId,
            'type' => $payload['type'],
            'amount' => $payload['amount'],
            'note' => $payload['note'],
        ]);

        if ($payload['type'] === 'deposit') {
            $saving->increment('current_amount', $payload['amount']);
        } else {
            $saving->decrement('current_amount', $payload['amount']);
        }
    }


}