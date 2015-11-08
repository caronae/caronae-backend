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
			return 'token ' . $token . ' já existe';
		}
		
		$user = new User();

		$user->name = $name;
		$user->token = $token;
		$user->profile = "Perfil padrão";
		$user->course = "Curso padrão";

		$user->save();
		
		return $name . ' cadastrado com o token ' . $token;
	}
	
	public function update(Request $request, $id)
    {
        $decode = json_decode($request->getContent());

        $user = User::where('token', $request->header('token'))->first();

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

    public function auth(Request $request) {
        $decode = json_decode($request->getContent());

        $user = User::where('token', $decode->token)->first();
		if ($user == null) {
			return;
		}
		
        $rides = $user->rides;
		
		$drivingRides = array();
		foreach($rides as $ride) {
			if ($ride->pivot->status == 'driver') {
				array_push($drivingRides, $ride);
			}
		}
		
		$resultJson = array("user" => $user, "rides" => $drivingRides);

        return $resultJson;
    }
	
	public function saveGcmToken(Request $request) {
		$user = User::where('token', $request->header('token'))->first();
		$decode = json_decode($request->getContent());
		
		$user->gcm_token = $decode->token;
		
		$user->save();
	}
}
