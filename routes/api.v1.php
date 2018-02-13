<?php

// User

Route::post('users', 'UserController@store')->middleware('api.institution');
Route::post('users/login', 'UserController@login');

Route::middleware('api.v1.auth')->group(function () {

    Route::get('users/{id}/mutualFriends', 'UserController@getMutualFriends');

    Route::middleware('api.v1.userMatchesRequestedUser')->group(function () {
        Route::put('users/{user}', 'UserController@update');
        Route::get('users/{user}/rides', 'UserController@getRides');
    });

});



// Ride

Route::get('rides', 'RideController@index');
Route::post('rides', 'RideController@store');
Route::delete('rides/{rideId}', 'RideController@delete');
Route::delete('rides/allFromUser/{userId}/{going}', 'RideController@deleteAllFromUser');
Route::delete('rides/allFromRoutine/{routineId}', 'RideController@deleteAllFromRoutine');
Route::post('rides/requestJoin', 'RideController@requestJoin');
Route::get('rides/getRequesters/{rideId}', 'RideController@getRequesters');
Route::post('rides/answerJoinRequest', 'RideController@answerJoinRequest');
Route::post('rides/leaveRide', 'RideController@leaveRide');
Route::post('rides/finishRide', 'RideController@finishRide');
Route::get('rides/getRidesHistory', 'RideController@getRidesHistory');
Route::get('rides/getRidesHistoryCount/{userId}', 'RideController@getRidesHistoryCount');
Route::post('rides/saveFeedback', 'RideController@saveFeedback');
Route::get('rides/validateDuplicate', 'RideController@validateDuplicate');
Route::get('rides/{ride}', 'RideController@show');
Route::post('rides/{ride}/messages', 'RideController@sendChatMessage');
Route::get('rides/{ride}/messages', 'RideController@getChatMessages');

// Etc

Route::get('places', 'PlaceController@index');
Route::post('falae/sendMessage', 'FalaeController@sendMessage');

