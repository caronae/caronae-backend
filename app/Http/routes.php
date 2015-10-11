<?php

use App\User;

Route::resource('user', 'UserController');
Route::resource('ride', 'RideController');

Route::post('auth', 'UserController@auth');

Route::post('ride/list', 'RideController@listAll');
Route::post('ride/requestJoin', 'RideController@requestJoin');
Route::post('ride/delete', 'RideController@delete');

// rota para testar os resultados no banco
Route::get('db', function() {
    $user = User::all()->first();

    var_dump($user);
});

Route::get('create/{name}/{token}', function($name, $token) {
    $user = new User();

    $user->name = $name;
    $user->token = $token;

    $user->save();
});

//rota padrão do laravel podemos excluir depois
Route::get('/', function () {
    return View::make('welcome');
});

