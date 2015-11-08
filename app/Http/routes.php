<?php

Route::resource('user', 'UserController');
Route::resource('ride', 'RideController');

Route::get('user/signup/{name}/{token}', 'UserController@signUp');
Route::post('user/auth', 'UserController@auth');
Route::post('user/saveGcmToken', 'UserController@saveGcmToken');

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

