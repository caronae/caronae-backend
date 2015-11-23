<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

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
        $drivingRides = $user->rides()->where('status', 'driver')->get();
		
		return array("user" => $user, "rides" => $drivingRides);
    }
	
	public function update(Request $request) {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
		if ($user == null) {
			return response()->json(['error'=>'User ' . $request->header('token') . ' token not authorized.'], 403);
		}

        $user->name = $decode->name;
        $user->profile = $decode->profile;
        $user->course = $decode->course;
        $user->phone_number = $decode->phone_number;
        $user->email = $decode->email;
        $user->location = $decode->location;
        $user->car_owner = $decode->car_owner;
        $user->car_model = $decode->car_model;
        $user->car_color = $decode->car_color;
        $user->car_plate = $decode->car_plate;

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
}
