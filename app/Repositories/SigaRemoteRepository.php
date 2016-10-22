<?php

namespace App\Repositories;

use App\Exceptions\SigaException;

class SigaRemoteRepository implements SigaInterface
{
    public function getProfileById($id)
    {
        return $this->search('IdentificacaoUFRJ', $id);
    }

    protected function search($searchKey, $searchValue)
    {
        $context = stream_context_create(['http' => ['timeout' => 2]]);
        $intranetResponseJSON = @file_get_contents('http://146.164.2.117:9200/_search?q=' . $searchKey . ':' . $searchValue, FILE_TEXT, $context);

        // Check if the connection was successful
        if ($intranetResponseJSON == false) {
            throw new SigaException('Failed to connect to SIGA.');
        }

        // Decode JSON
        $intranetResponse = json_decode($intranetResponseJSON);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SigaException('Error decoding response from SIGA.');
        }

        // Check if we found a hit
        if (empty($intranetResponse->hits->hits)) {
            throw new SigaException('No UFRJ profile found with this identification.');
        }

        $intranetUser = $intranetResponse->hits->hits[0]->_source;

        // Check if the extracted user has all the required fields
        if (!isset($intranetUser->nome) || !isset($intranetUser->nomeCurso) ||
        !isset($intranetUser->situacaoMatricula) || !isset($intranetUser->nivel)) {
            throw new SigaException('Unexpected response from SIGA.');
        }

        // Check if the user is still enrolled
        if ($intranetUser->situacaoMatricula != "Ativa") {
            throw new SigaException('User does not have an active profile from SIGA.');
        }

        return $intranetUser;
    }

}
