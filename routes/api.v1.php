<?php

// User

Route::post('users', 'UserController@store');
Route::put('users', 'UserController@update');
Route::post('users/login', 'UserController@login');
Route::get('users/{user}/rides', 'UserController@getRides');
Route::get('users/{user}/offeredRides', 'UserController@getOfferedRides');
Route::get('users/{user}/pendingRides', 'UserController@getPendingRides');
Route::get('users/{id}/mutualFriends', 'UserController@getMutualFriends');

/** @deprecated  */
Route::put('users/saveFaceId', 'UserController@saveFacebookId');
/** @deprecated  */
Route::put('users/saveProfilePicUrl', 'UserController@saveProfilePicUrl');


// Ride

Route::get('rides', 'RideController@index');
Route::post('ride', 'RideController@store');
Route::delete('ride/{rideId}', 'RideController@delete');
Route::delete('ride/allFromUser/{userId}/{going}', 'RideController@deleteAllFromUser');
Route::delete('ride/allFromRoutine/{routineId}', 'RideController@deleteAllFromRoutine');
Route::post('ride/listFiltered', 'RideController@listFiltered');
Route::post('ride/requestJoin', 'RideController@requestJoin');
Route::get('ride/getRequesters/{rideId}', 'RideController@getRequesters');
Route::post('ride/answerJoinRequest', 'RideController@answerJoinRequest');
Route::get('ride/getMyActiveRides', 'RideController@getMyActiveRides');
Route::post('ride/leaveRide', 'RideController@leaveRide');
Route::post('ride/finishRide', 'RideController@finishRide');
Route::get('ride/getRidesHistory', 'RideController@getRidesHistory');
Route::get('ride/getRidesHistoryCount/{userId}', 'RideController@getRidesHistoryCount');
Route::post('ride/saveFeedback', 'RideController@saveFeedback');
Route::get('ride/validateDuplicate', 'RideController@validateDuplicate');
Route::get('ride/{ride}', 'RideController@show');
Route::post('ride/{ride}/chat', 'RideController@sendChatMessage');
Route::get('ride/{ride}/chat', 'RideController@getChatMessages');


// Etc

Route::get('places', 'PlaceController@index');
Route::post('falae/sendMessage', 'FalaeController@sendMessage');

