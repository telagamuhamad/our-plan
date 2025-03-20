<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getAllUser()
    {
        return $this->model->get();
    }
}