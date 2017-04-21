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

// Logs interface

Route::name('logs')->get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

// Administrative pages

Route::get('/', [
    'as' => 'home',
    'uses' => 'AdminController@getIndex'
]);

Route::get('edit', 'AdminController@getEdit');
Route::post('edit', 'AdminController@postEdit');

Route::get('users', 'UserController@index');
Route::get('users.json', 'UserController@indexJson');
Route::get('users.excel', 'UserController@indexExcel');
Route::post('user/{id}/banish', 'UserController@banish');
Route::post('user/{id}/unban', 'UserController@unban');

Route::get('ranking/better-feedback', 'RankingController@betterFeedback');
Route::get('ranking/better-feedback.json', 'RankingController@betterFeedbackJson');
Route::get('ranking/better-feedback.excel', 'RankingController@betterFeedbackExcel');

Route::get('ranking/greater-riders', 'RankingController@greaterRiders');
Route::get('ranking/greater-riders.json', 'RankingController@greaterRidersJson');
Route::get('ranking/greater-riders.excel', 'RankingController@greaterRidersExcel');

Route::get('ranking/greater-drivers-riders', 'RankingController@greaterDriversRiders');
Route::get('ranking/greater-drivers-riders.json', 'RankingController@greaterDriversRidersJson');
Route::get('ranking/greater-drivers-riders.excel', 'RankingController@greaterDriversRidersExcel');

Route::get('ranking/greater-average-occupancy', 'RankingController@greaterAverageOccupancy');
Route::get('ranking/greater-average-occupancy.json', 'RankingController@greaterAverageOccupancyJson');
Route::get('ranking/greater-average-occupancy.excel', 'RankingController@greaterAverageOccupancyExcel');

Route::get('rides', 'RideController@index');
Route::get('rides.json', 'RideController@indexJson');
Route::get('rides.excel', 'RideController@indexExcel');

Route::get('riders/{ride_id}', 'RideController@riders');

Route::get('carbonTax', 'CarbonTaxController@carbonTax');
