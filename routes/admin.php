<?php

Route::name('home')->get('/', function() {
	return redirect()->route('dashboard');
});

Route::name('dashboard')->get('dashboard', 'HomeController@index');

CRUD::resource('users', 'UserController')->with(function() {
    Route::post('users/{user}/ban', 'UserController@ban');
    Route::post('users/{user}/unban', 'UserController@unban');
});

Route::name('rides')->get('rides', 'RideController@index');
Route::get('rides.json', 'RideController@indexJson');
Route::name('ride')->get('rides/{ride}', 'RideController@show');

CRUD::resource('admins', 'AdminController');
CRUD::resource('institutions', 'InstitutionController');
CRUD::resource('hubs', 'HubController');
CRUD::resource('zones', 'ZoneController');
CRUD::resource('zones/{zone}/neighborhoods', 'NeighborhoodController');
CRUD::resource('neighborhoods', 'NeighborhoodController');
