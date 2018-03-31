<?php

Route::get('login', 'LoginController@index')->name('chave');
Route::post('refreshToken', 'LoginController@refreshToken')->name('refreshToken');

Route::get('/', function() {
    return view('home.landing');
});

Route::get('carona/{id}', 'RideController@show');
