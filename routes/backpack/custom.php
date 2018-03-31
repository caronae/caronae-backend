<?php

Route::group([
    'namespace'  => 'Caronae\Http\Controllers\Admin',
    'middleware' => ['web', 'admin'],
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
], function () {
    CRUD::resource('users', 'UserController')->with(function() {
        Route::post('users/{user}/ban', 'UserController@ban');
        Route::post('users/{user}/unban', 'UserController@unban');
    });

    CRUD::resource('rides', 'RideController');
    CRUD::resource('admins', 'AdminController');
    CRUD::resource('institutions', 'InstitutionController');
    CRUD::resource('hubs', 'HubController');
    CRUD::resource('zones', 'ZoneController');
    CRUD::resource('zones/{zone}/neighborhoods', 'NeighborhoodController');
    CRUD::resource('neighborhoods', 'NeighborhoodController');
});
