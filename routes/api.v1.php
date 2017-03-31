<?php
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// User

Route::get('user/signup/intranet/{idUFRJ}/{token}', 'UserController@signUpIntranet');
Route::post('users', 'UserController@store');
Route::post('user/login', 'UserController@login');
Route::put('user', 'UserController@update');
Route::get('user/{user}/offeredRides', 'UserController@getOfferedRides');
Route::put('user/saveGcmToken', 'UserController@saveGcmToken');
Route::put('user/saveFaceId', 'UserController@saveFaceId');
Route::put('user/saveProfilePicUrl', 'UserController@saveProfilePicUrl');
Route::get('user/{id}/mutualFriends', 'UserController@getMutualFriends');
Route::get('user/intranetPhotoUrl', 'UserController@getIntranetPhotoUrl');
Route::get('user/intranetPhoto/{hash}', function($hash) {
	return redirect()->away('https://sigadocker.ufrj.br:8090/' . $hash, 308);
});


// Ride

Route::get('rides', 'RideController@index');
Route::get('ride/all', 'RideController@listAll');
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
Route::post('ride/{ride}/chat', 'RideController@sendChatMessage');
Route::get('ride/{ride}/chat', 'RideController@getChatMessages');


// Falae

Route::post('falae/sendMessage', 'FalaeController@sendMessage');
