<?php

use App\User;

Route::resource('user', 'UserController');
Route::resource('ride', 'RideController');

Route::post('auth', 'UserController@auth');

Route::post('ride/list', 'RideController@listFiltered');
Route::post('ride/requestJoin', 'RideController@requestJoin');
Route::post('ride/delete', 'RideController@delete');

// rota para testar os resultados no banco
Route::get('db', function() {
    $user = User::all()->first();

    var_dump($user);
});

Route::get('signup/{name}', function($name) {
	if (User::where('name', $name)->count() > 0) {
		return $name . ' já existe';
	}
	
    $user = new User();

    $user->name = $name;
    $user->token = $name;

    $user->save();
	
	return $name . ' cadastrado';
});

//rota padrão do laravel podemos excluir depois
Route::get('/', function () {
    return View::make('welcome');
});

