<?php

use App\User;

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

// rota para testar os resultados no banco
Route::get('db', function() {
    $user = User::all()->first();

    var_dump($user);
});

Route::get('signup/{name}', function($name) {
	if (User::where('name', $name)->count() > 0) {
		return $name . ' já existe';
	}
	if (User::where('token', $name)->count() > 0) {
		return 'token ' . $name . ' já existe';
	}
	
    $user = new User();

    $user->name = $name;
    $user->token = $name;
    $user->profile = "Perfil padrão";
    $user->course = "Curso padrão";

    $user->save();
	
	return $name . ' cadastrado';
});

Route::get('signup/{name}/{token}', function($name, $token) {
	if (User::where('name', $name)->count() > 0) {
		return $name . ' já existe';
	}
	if (User::where('token', $token)->count() > 0) {
		return $token . ' já existe';
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

