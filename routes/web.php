<?php

Route::get('login', 'LoginController@index')->name('chave');
Route::post('refreshToken', 'LoginController@refreshToken')->name('refreshToken');

Route::get('/', function() {
    return view('home.landing');
});

Route::get('carona/{id}', 'RideController@show');

// Static pages
/** @deprecated */

Route::get('static_pages/sobre.html', function(){
    return redirect()->away('https://caronae.org/sobre_mobile.html');
});

Route::get('static_pages/termos.html', function(){
    return redirect()->away('https://caronae.org/termos_mobile.html');
});

Route::get('static_pages/faq.html', function(){
    return redirect()->away('https://caronae.org/faq.html?mobile');
});
