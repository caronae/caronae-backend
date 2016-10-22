<?php

namespace App\Services;

use App\Repositories\SigaInterface;
use App\Exceptions\SigaException;

class SigaService
{
    protected $sigaRepo;

    public function __construct(SigaInterface $sigaRepo)
    {
        $this->sigaRepo = $sigaRepo;
    }

    public function getProfileById($id)
    {
        // TODO: implement business logic
        return $this->sigaRepo->getProfileById($id);
    }

}
