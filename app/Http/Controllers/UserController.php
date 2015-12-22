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
	
	public function getMutualFriends(Request $request, $id, $fbtoken) {
		$user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User ' . $request->header('token') . ' token not authorized.'], 403);
		}

		$queryUser = User::find($id);
		if ($queryUser == null) {
			return response()->json(['error'=>'Requested user not found.'], 400);
		}
		if (empty($queryUser->face_id)) {
			return response()->json(['error'=>'Requested user does not have face id.'], 400);
		}

		$fb = new Facebook\Facebook([
			'app_id' => '933455893356973',
			'app_secret' => '007b9930ed5a15c407c44768edcbfebd',
			'default_graph_version' => 'v2.5'
		]);
		
		try {
			$response = $fb->get('/' . $queryUser->face_id . '?fields=context.fields(mutual_friends)', $fbtoken);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
			return response()->json(['error'=>'Facebook Graph returned an error: ' . $e->getMessage()], 500);
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  // When validation fails or other local issues
			return response()->json(['error'=>'Facebook SDK returned an error: ' . $e->getMessage()], 500);
		}

		$mutualFriendsFB = $response->getGraphObject()['context']['mutual_friends'];
		// Array will hold only the Facebook IDs of the mutual friends
		$friendsFacebookIds = [];
		foreach ($mutualFriendsFB as $friendFB) {
			$friendsFacebookIds[] = $friendFB['id'];
		}
		
		$mutualFriends = User::whereIn('face_id', $friendsFacebookIds)->get();
		return response()->json($mutualFriends);
    }
}
