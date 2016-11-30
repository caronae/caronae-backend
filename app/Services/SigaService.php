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

        $pictureUrl = $profile->urlFoto;

        // See if URL is a valid photo url from siga and proxy it to our server
        if (preg_match('/(?:146\.164\.2\.117:8090\/)(?P<hash>.+)/', $pictureUrl, $matches)) {
            $pictureUrl = 'https://api.caronae.ufrj.br/user/intranetPhoto/' . $matches['hash'];
        }

        return $pictureUrl;
    }

}
