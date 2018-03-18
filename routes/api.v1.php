<?php

// User

Route::post('users', 'UserController@store')->middleware('api.institution');
Route::post('users/login', 'UserController@login');

Route::middleware('api.v1.auth')->group(function () {

    Route::get('users/{id}/mutualFriends', 'UserController@getMutualFriends');
    Route::get('users/{user}/rides/history', 'UserController@getRidesHistory');

    Route::middleware('api.v1.userMatchesRequestedUser')->group(function () {
        Route::get('users/{user}', 'UserController@show');
        Route::put('users/{user}', 'UserController@update');
        Route::get('users/{user}/rides', 'UserController@getRides');
        Route::get('users/{user}/token', 'UserController@getToken');
    });

});



// Ride

Route::get('rides', 'RideController@index');
Route::post('rides', 'RideController@store');
Route::delete('rides/{rideId}', 'RideController@delete');
Route::delete('rides/allFromRoutine/{routineId}', 'RideController@deleteAllFromRoutine');
Route::post('rides/{ride}/requests', 'RideController@createRequest');
Route::get('rides/{ride}/requests', 'RideController@getRequests');
Route::put('rides/{ride}/requests', 'RideController@updateRequest');
Route::post('rides/leaveRide', 'RideController@leaveRide');
Route::post('rides/finishRide', 'RideController@finishRide');
Route::get('rides/validateDuplicate', 'RideController@validateDuplicate');
Route::get('rides/{ride}', 'RideController@show');
Route::post('rides/{ride}/messages', 'RideController@sendChatMessage');
Route::get('rides/{ride}/messages', 'RideController@getChatMessages');

// Etc

Route::get('places', 'PlaceController@index');
Route::post('falae/messages', 'FalaeController@sendMessage');

