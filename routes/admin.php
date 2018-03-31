<?php

Route::name('home')->get('/', function() {
	return redirect()->route('dashboard');
});

Route::name('dashboard')->get('dashboard', 'HomeController@index');
