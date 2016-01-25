<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

use Facebook;

class UserController extends Controller
{
    public function signUp($name, $token) {
		if (User::where('token', $token)->count() > 0) {
			return response()->json(['error'=>'User token already exists.'], 409);
		}
		
		$user = new User();

		$user->name = $name;
		$user->token = $token;
		$user->profile = "Perfil padrão";
		$user->course = "Curso padrão";

		$user->save();
		
		return $name . ' cadastrado com o token ' . $token;
	}

    public function signUpIntranet($idUFRJ, $token) {
		if (User::where('token', $token)->count() > 0) {
			return response()->json(['error'=>'User token already exists.'], 409);
		}

		$context = stream_context_create(['http' => ['timeout' => 2]]);
		$intranetResponseJSON = @file_get_contents('http://146.164.2.117:9200/_search?q=IdentificacaoUFRJ:' . $idUFRJ, FILE_TEXT, $context);

		// Check if the connection was successful
		if ($intranetResponseJSON == false) {
			return response()->json(['error'=>'Failed to connect to Intranet.'], 500);
		}
		
		$intranetResponse = json_decode($intranetResponseJSON);

		// Check if we received the expected result with a hit
		if ($intranetResponse->hits->hits && count($intranetResponse->hits->hits) > 0) {
			$intranetUser = $intranetResponse->hits->hits[0]->_source;
			$intranetUserType = $intranetResponse->hits->hits[0]->_type;

			// Check if the extracted user has all required fields
			if (!isset($intranetUser->nome) || !isset($intranetUser->nomeCurso) || 
				!isset($intranetUser->situacaoMatricula) || !$intranetUserType) {
				return response()->json(['error'=>'Unexpected response from intranet.'], 500);
			}

			// Check if the user is still enrolled
			if ($intranetUser->situacaoMatricula != "Ativa") {
				return response()->json(['error'=>'User does not have an active ID from intranet.'], 403);
			}
		} else {
			return response()->json(['error'=>'No UFRJ profile found with this identification.'], 403);
		}

		$user = new User();

		$user->name = mb_convert_case($intranetUser->nome, MB_CASE_TITLE, "UTF-8");
		$user->token = $token;
		$user->id_ufrj = $idUFRJ;
		$user->profile = ucfirst($intranetUserType);
		$user->course = $intranetUser->nomeCurso;

		if (isset($intranetUser->urlFoto) && $intranetUser->urlFoto != '') {
			list($photoHost, $photoHash) = explode('/',$intranetUser->urlFoto);
			if ($photoHost == '146.164.2.117:8090') {
				$user->profile_pic_url = 'http://web1.tic.ufrj.br/caronae/user/intranetPhoto/' . $photoHash;
			}
		}

		$user->save();
		
		return $user->name . ' cadastrado com o token ' . $token;
	}
	
    public function login(Request $request) {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $decode->token)->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		
		//get user's rides as driver
		$matchThese = ['status' => 'driver', 'done' => false];
        $drivingRides = $user->rides()->where($matchThese)->get();
		
		return array("user" => $user, "rides" => $drivingRides);
    }
	
	public function update(Request $request) {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User ' . $request->header('token') . ' token not authorized.'], 403);
		}

        $user->profile = $decode->profile;
        $user->course = $decode->course;
        $user->phone_number = $decode->phone_number;
        $user->email = $decode->email;
        $user->location = $decode->location;
        $user->car_owner = $decode->car_owner;
        $user->car_model = $decode->car_model;
        $user->car_color = $decode->car_color;
        $user->car_plate = $decode->car_plate;
        if (@$decode->profile_pic_url != "") {
        	$user->profile_pic_url = $decode->profile_pic_url;
        }
        
        $user->save();
	}

	public function saveGcmToken(Request $request) {
		$decode = json_decode($request->getContent());
		$user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		
		$user->gcm_token = $decode->token;
		
		$user->save();
    }
	
	public function saveFaceId(Request $request) {
		$decode = json_decode($request->getContent());
		$user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		
		if ($decode->id) $user->face_id = $decode->id;
		
		$user->save();
    }
	
	public function saveProfilePicUrl(Request $request) {
		$decode = json_decode($request->getContent());
		$user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User token not authorized.'], 403);
		}
		
		$user->profile_pic_url = $decode->url;
		
		$user->save();
    }
	
	public function getMutualFriends(Request $request, $fbid) {
		$user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User ' . $request->header('token') . ' token not authorized.'], 403);
		}

		$fbtoken = $request->header('Facebook-Token');
		if ($fbtoken == null) {
			return response()->json(['error'=>'User\'s Facebook token missing.'], 403);
		}

		$fb = new Facebook\Facebook([
			'app_id' => '933455893356973',
			'app_secret' => '007b9930ed5a15c407c44768edcbfebd',
			'default_graph_version' => 'v2.5'
		]);
		
		try {
			$response = $fb->get('/' . $fbid . '?fields=context.fields(mutual_friends)', $fbtoken);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
			return response()->json(['error'=>'Facebook Graph returned an error: ' . $e->getMessage()], 500);
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  // When validation fails or other local issues
			return response()->json(['error'=>'Facebook SDK returned an error: ' . $e->getMessage()], 500);
		}

		$mutualFriendsFB = $response->getGraphObject()['context']['mutual_friends'];
		$totalFriendsCount = $mutualFriendsFB->getMetaData()['summary']['total_count'];

		// Array will hold only the Facebook IDs of the mutual friends
		$friendsFacebookIds = [];
		foreach ($mutualFriendsFB as $friendFB) {
			$friendsFacebookIds[] = $friendFB['id'];
		}
		
		$mutualFriends = User::whereIn('face_id', $friendsFacebookIds)->get();
		return response()->json(["total_count" => $totalFriendsCount, "mutual_friends" => $mutualFriends]);
    }

    public function loadIntranetPhoto($hash) {
    	$context = stream_context_create(['http' => ['timeout' => 2]]);
		$intranetResponseRaw = @file_get_contents('http://146.164.2.117:8090/' . $hash, FILE_BINARY, $context);

		// Check if the connection was successful
		if ($intranetResponseRaw == false) {
			return response()->json(['error'=>'Failed to connect to Intranet photos database.'], 500);
		}

		// TODO: Confirm if the photo is always a JPG
		return response($intranetResponseRaw)->header('Content-Type', 'image/jpg');
		
    }
}
