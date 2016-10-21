<?php

namespace App\Services;

use App\Exceptions\SigaException;

class SigaService
{
    public function search($mock = false)
	{
		if ($mock) {
			$intranetResponseJSON = '{
			  "took": 1,
			  "timed_out": false,
			  "_shards": {
			    "total": 5,
			    "successful": 5,
			    "failed": 0
			  },
			  "hits": {
			    "total": 0,
			    "max_score": 10.063173,
			    "hits": [
			      {
			        "_index": "alunos_regularmente_matriculados",
			        "_type": "aluno",
			        "_id": "11628052775",
			        "_score": 10.063173,
			        "_source": {
			          "nivel": "Graduação",
			          "codCurso": "3101070000",
			          "nomeCurso": "Bacharelado em Ciência da Computação",
			          "matriculadre": "111318912",
			          "IdentificacaoUFRJ": "11628052775",
			          "nome": "MÁRIO ALBERTO CECCHI RADUAN",
			          "situacaoMatricula": "Ativa",
			          "urlFoto": "146.164.2.117:8090/E332EACE-7F00-0001-01EC-B0F035BE28FB",
			          "alunoServidor": "0"
			        }
			      }
			    ]
			  }
			}';
		} else {
			$context = stream_context_create(['http' => ['timeout' => 2]]);
			$intranetResponseJSON = @file_get_contents('http://146.164.2.117:9200/_search?q=' . $searchKey . ':' . $searchValue . '&from=' . $from . '&size=' . $size, FILE_TEXT, $context);
		}

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
