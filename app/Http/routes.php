<?php

use App\User;

Route::resource('user', 'UserController');

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

