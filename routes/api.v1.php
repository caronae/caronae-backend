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
        Route::post('users/{user}/profile_picture', 'UserController@uploadProfilePicture');
        Route::get('users/{user}/rides', 'UserController@getRides');
        Route::get('users/{user}/token', 'UserController@getToken');
    });


    Route::get('rides', 'RideController@index');
    Route::post('rides', 'RideController@store');
    Route::delete('rides/allFromRoutine/{routineId}', 'RideController@deleteAllFromRoutine');
    Route::post('rides/{ride}/requests', 'RideController@createRequest');
    Route::get('rides/validateDuplicate', 'RideController@validateDuplicate');
    Route::get('rides/{ride}', 'RideController@show');

    Route::middleware('api.v1.userBelongsToRide')->group(function () {
        Route::post('rides/{ride}/leave', 'RideController@leaveRide');
        Route::post('rides/{ride}/messages', 'ChatController@sendMessage');
        Route::get('rides/{ride}/messages', 'ChatController@getMessages');
    });

    Route::middleware('api.v1.userIsTheDriver')->group(function () {
        Route::get('rides/{ride}/requests', 'RideController@getRequests');
        Route::put('rides/{ride}/requests', 'RideController@updateRequest');
        Route::post('rides/{ride}/finish', 'RideController@finishRide');
    });

    Route::get('places', 'PlaceController@index');
    Route::post('falae/messages', 'FalaeController@sendMessage');
});
