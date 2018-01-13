<?php
/** @deprecated  */

// User

Route::post('users', 'UserController@store');
Route::post('user/login', 'UserController@login');
Route::put('user', 'UserController@update');
Route::get('user/{user}/rides', 'UserController@getRides');
Route::get('user/{user}/offeredRides', 'UserController@getOfferedRides');
Route::get('user/{user}/pendingRides', 'UserController@getPendingRides');
Route::put('user/saveFaceId', 'UserController@saveFacebookId');
Route::put('user/saveProfilePicUrl', 'UserController@saveProfilePicUrl');
Route::get('user/{id}/mutualFriends', 'UserController@getMutualFriends');


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
