<?php

Route::name('home')->get('/', function() {
	return redirect()->route('dashboard');
});

Route::name('dashboard')->get('dashboard', 'HomeController@index');
Route::name('self-service-token')->get('self-service-token', 'TokenController@index');
Route::name('self-service-token-new')->post('self-service-token/new', 'TokenController@new');
