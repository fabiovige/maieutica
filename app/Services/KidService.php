<?php

namespace App\Services;

use App\Repositories\KidRepository;

class KidService
{
    protected $kidRepository;

    public function __construct(KidRepository $kidRepository)
    {
        $this->kidRepository = $kidRepository;
    }

    public function getAllKids()
    {
        return $this->kidRepository->all();
    }

    public function createKid(array $data)
    {
        return $this->kidRepository->create($data);
    }

    // Adicione outros métodos conforme necessário
}
