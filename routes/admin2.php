<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/

Route::get('/', function() {
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
