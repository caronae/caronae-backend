<?php
/** @deprecated */

// User

Route::post('users', 'UserController@store')->middleware('api.institution');
Route::post('user/login', 'UserController@login');

Route::middleware('api.v1.auth')->group(function () {

    Route::put('user', 'UserController@update');
    Route::get('user/{id}/mutualFriends', 'UserController@getMutualFriends');

    Route::middleware('api.v1.userMatchesRequestedUser')->group(function () {
        Route::get('user/{user}/rides', 'UserController@getRides');
    });


    Route::get('rides', 'RideController@index');
    Route::post('ride', 'RideController@store');
    Route::delete('ride/allFromRoutine/{routineId}', 'RideController@deleteAllFromRoutine');
    Route::get('ride/getRequesters/{rideId}', 'RideController@getRequests');
    Route::get('ride/validateDuplicate', 'RideController@validateDuplicate');
    Route::get('ride/{ride}', 'RideController@show');

    Route::middleware('api.v1.userBelongsToRide')->group(function () {
        Route::get('ride/{ride}/chat', 'ChatController@getMessages');
        Route::post('ride/{ride}/chat', 'ChatController@sendMessage');
    });

    Route::get('places', 'PlaceController@index');
    Route::post('falae/sendMessage', 'FalaeController@sendMessage');
});

