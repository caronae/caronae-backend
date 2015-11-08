<?php

Route::resource('ride', 'RideController');

Route::get('user/signup/{name}/{token}', 'UserController@signUp'); //mudar essa rota de get para post assim q possível
Route::post('user/login', 'UserController@login');
Route::put('user/update', 'UserController@update');
Route::put('user/saveGcmToken', 'UserController@saveGcmToken');
Route::put('user/clearGcmToken', 'UserController@clearGcmToken');

Route::post('ride/list', 'RideController@listFiltered');
Route::post('ride/requestJoin', 'RideController@requestJoin');
Route::post('ride/delete', 'RideController@delete');
Route::post('ride/getRequesters', 'RideController@getRequesters');
Route::post('ride/answerJoinRequest', 'RideController@answerJoinRequest');
Route::post('ride/getMyActiveRides', 'RideController@getMyActiveRides');
Route::post('ride/leaveRide', 'RideController@leaveRide');

//rota padrão do laravel podemos excluir depois
Route::get('/', function () {
    return View::make('welcome');
});

