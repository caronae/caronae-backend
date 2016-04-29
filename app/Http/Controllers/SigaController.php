<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class SigaController extends Controller
{
	private $auth_key = 'token=AYAeG!*knMjqLF0[!ND\xs7t3Uv]16d';

    public function search(Request $request) {
    	// Test if is running through SSL
    	if (!is_secure()) {
			return response()->json(['error'=>'Route not allowed without SSL.'], 403);
    	}

    	// Test if authorization token is correct
    	$authorization = $request->header('Authorization');
		if ($authorization == null || $authorization != $this->auth_key) {
			return response()->json(['error'=>'Unauthorized.'], 403);
		}

		// Decode search
        $searchKey = Input::get('field');
		if ($searchKey == 'cpf') $searchKey = 'IdentificacaoUFRJ';
        $searchValue = urlencode(Input::get('value'));
		if (!$searchKey || !$searchValue) {
			return response()->json(['error'=>'Missing search parameters.'], 400);
		}
		$from = Input::get('from');
		if (!$from) $from = 0;
		$size = Input::get('size');
		if (!$size) $size = 10;

		$context = stream_context_create(['http' => ['timeout' => 2]]);
		$intranetResponseJSON = @file_get_contents('http://146.164.2.117:9200/_search?q=' . $searchKey . ':' . $searchValue . '&from=' . $from . '&size=' . $size, FILE_TEXT, $context);

		// Check if the connection was successful
		if ($intranetResponseJSON == false) {
			return response()->json(['error'=>'Failed to connect to Intranet.'], 500);
		}
		
		$intranetResponse = json_decode($intranetResponseJSON);
		return response()->json($intranetResponse);
    }

}

function is_secure() {
	return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
}
