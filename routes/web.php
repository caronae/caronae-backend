<?php

Route::get('login', 'LoginController@index')->name('chave');
Route::post('refreshToken', 'LoginController@refreshToken')->name('refreshToken');

Route::get('/', function() {
    return '';
});

Route::get('carona/{ride}', 'RideController@showWeb');

// Static pages

Route::get('static_pages/sobre.html', function(){
    return view('static_pages/sobre');
});

Route::get('static_pages/termos.html', function(){
    return view('static_pages/termos');
});

Route::get('static_pages/faq.html', function(){
    return view('static_pages/faq');
});
