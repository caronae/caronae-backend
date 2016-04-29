<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

class SigaController extends Controller
{
	private $auth_key = 'token=AYAeG!*knMjqLF0[!ND\xs7t3Uv]16d';

    public function search(Request $request, $searchKey, $searchValue) {
    	// Test if is running through SSL
    	if (!is_secure()) {
			return response()->json(['error'=>'Route not allowed without SSL.'], 403);
    	}

    	// Test if authorization token is correct
    	$authorization = $request->header('Authorization');
		if ($authorization == null || $authorization != $this->auth_key) {
			return response()->json(['error'=>'Unauthorized.'], 403);
		}

		if ($searchKey == 'cpf') $searchKey = 'IdentificacaoUFRJ';

		$context = stream_context_create(['http' => ['timeout' => 2]]);
		$intranetResponseJSON = @file_get_contents('http://146.164.2.117:9200/_search?q=' . $searchKey . ':' . $searchValue, FILE_TEXT, $context);

		// Check if the connection was successful
		if ($intranetResponseJSON == false) {
			return response()->json(['error'=>'Failed to connect to Intranet.'], 500);
		}
		
		$intranetResponse = json_decode($intranetResponseJSON);

		response()->json($intranetResponse);
    }

}

function is_secure() {
	return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
}
