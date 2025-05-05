<?php

namespace App\Services;

use App\Mail\SavingTransactionMail;
use App\Repositories\SavingRepository;
use App\Repositories\SavingTransactionRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Mail;

class SavingTransactionService{
    protected $repository;
    protected $savingRepository;
    protected $userRepository;

    public function __construct(SavingTransactionRepository $repository, SavingRepository $savingRepository, UserRepository $userRepository)
    {
        $this->repository = $repository;
        $this->savingRepository = $savingRepository;
        $this->userRepository = $userRepository;
    }

    public function getTransactionsBySavingId($savingId)
    {
        return $this->repository->getBySavingId($savingId);
    }

    public function addTransaction($saving, array $payload)
    {

        if ($payload['type'] === 'withdrawal' && $saving->current_amount < $payload['amount']) {
            throw new Exception('Saldo tidak cukup untuk melakukan penarikan.');
        }

        $this->repository->create([
            'saving_id' => $saving->id,
            'type' => $payload['type'],
            'amount' => $payload['amount'],
            'note' => $payload['note'],
            'actor_user_id' => $payload['actor_user_id']
        ]);

        if ($payload['type'] === 'deposit') {
            $saving->increment('current_amount', $payload['amount']);
        } else {
            $saving->decrement('current_amount', $payload['amount']);
        }
    }


}