<?php

// User

Route::post('users', 'UserController@store');
Route::put('users', 'UserController@update');
Route::post('users/login', 'UserController@login');
Route::get('users/{user}/rides', 'UserController@getRides');
Route::get('users/{user}/offeredRides', 'UserController@getOfferedRides');
Route::get('users/{user}/pendingRides', 'UserController@getPendingRides');
Route::get('users/{id}/mutualFriends', 'UserController@getMutualFriends');


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

