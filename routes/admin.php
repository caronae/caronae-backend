<?php

Route::name('home')->get('/', 'HomeController@index');
Route::name('dashboard')->get('dashboard', 'HomeController@dashboard');
Route::name('self-service-token')->get('self-service-token', 'TokenController@index');
Route::name('self-service-token-new')->post('self-service-token/new', 'TokenController@new');
