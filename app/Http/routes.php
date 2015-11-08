<?php

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

Route::get('user/signup/{name}/{token}', 'UserController@signUp');
Route::post('user/saveGcmToken', 'UserController@saveGcmToken');

//rota padrão do laravel podemos excluir depois
Route::get('/', function () {
    return View::make('welcome');
});

