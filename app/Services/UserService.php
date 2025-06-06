<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService{
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllUser()
    {
        return $this->repository->getAllUser();
    }
}