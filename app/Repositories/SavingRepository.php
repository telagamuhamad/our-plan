<?php

namespace App\Repositories;

use App\Models\Saving;

class SavingRepository{
    protected $model;

    public function __construct(Saving $saving)
    {
        $this->model = $saving;
    }

    public function getAllSavings()
    {
        return $this->model->get();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $payload)
    {
        return $this->model->create($payload);
    }

    public function update($id, array $payload)
    {
        $saving = $this->find($id);
        return $saving->update($payload);
    }

    public function delete($id)
    {
        $saving = $this->find($id);
        return $saving->delete();
    }
}