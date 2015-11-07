<?php

use App\User;
use Illuminate\Http\Request;

Route::resource('user', 'UserController');
Route::resource('ride', 'RideController');

Route::post('auth', 'UserController@auth');

Route::post('ride/list', 'RideController@listFiltered');
Route::post('ride/requestJoin', 'RideController@requestJoin');
Route::post('ride/delete', 'RideController@delete');
Route::post('ride/getRequesters', 'RideController@getRequesters');
Route::post('ride/answerJoinRequest', 'RideController@answerJoinRequest');
Route::post('ride/getMyActiveRides', 'RideController@getMyActiveRides');
Route::post('ride/leaveRide', 'RideController@leaveRide');

Route::post('gcmToken', function(Request $request) {
	$user = User::where('token', $request->header('token'))->first();
	$decode = json_decode($request->getContent());
	
	$user->gcm_token = $decode->token;
	
	$user->save();
});

// rota para testar os resultados no banco
Route::get('db', ['middleware' => 'jwt.auth', function() {
    $user = User::all()->first();

    var_dump($user);
}]);

Route::get('signup/{name}', function($name) {
	if (User::where('token', $name)->count() > 0) {
		return 'token ' . $name . ' já existe';
	}
	
    $user = new User();

    $user->name = $name;
    $user->token = $name;
    $user->profile = "Perfil padrão";
    $user->course = "Curso padrão";

    $user->save();
	
	return $name . ' cadastrado com o token ' . $name;
});

Route::get('signup/{name}/{token}', function($name, $token) {
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
});

//rota padrão do laravel podemos excluir depois
Route::get('/', function () {
    return View::make('welcome');
});

