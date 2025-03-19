<?php

namespace App\Repositories;

use App\Models\SavingTransaction;

class SavingTransactionRepository{
    protected $model;

    public function __construct(SavingTransaction $savingTransaction)
    {
        $this->model = $savingTransaction;
    }

    public function getBySavingId($savingId)
    {
        return $this->model->where('saving_id', $savingId)->orderBy('created_at', 'desc')->get();
    }

    public function findBySavingId($savingId)
    {
        
    }

    public function create(array $payload)
    {
        return $this->model->create($payload);
    }
}