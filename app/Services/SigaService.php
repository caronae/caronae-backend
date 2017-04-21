<?php

namespace Caronae\Services;

use Caronae\Repositories\SigaInterface;
use Caronae\Exceptions\SigaException;

class SigaService
{
    protected $sigaRepo;

    public function __construct(SigaInterface $sigaRepo)
    {
        $this->sigaRepo = $sigaRepo;
    }

    public function getProfileById($id)
    {
        return $this->sigaRepo->getProfileById($id);
    }

    public function getProfilePictureById($id)
    {
        $profile = $this->sigaRepo->getProfileById($id);

        if (empty($profile->urlFoto)) {
            throw new SigaException("SIGA profile picture not found.");
        }

        return $profile->urlFoto;
    }

}
