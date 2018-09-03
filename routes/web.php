<?php

Route::get('/', 'HomeController@index');

Route::get('login', 'LoginController@index')->name('chave');
Route::post('refreshToken', 'LoginController@refreshToken')->name('refreshToken');

Route::get('carona/{id}', 'RideController@show');
