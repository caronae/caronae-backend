<?php

Route::get('login', 'LoginController@index')->name('chave');
Route::post('refreshToken', 'LoginController@refreshToken')->name('refreshToken');

Route::get('/', function() {
    return '';
});

Route::get('carona/{id}', 'RideController@showWeb');

// Static pages
// (redirect temporário)

Route::get('static_pages/sobre.html', function(){
    return redirect()->away('https://caronae.com.br/sobre_mobile.html');
});

Route::get('static_pages/termos.html', function(){
    return redirect()->away('https://caronae.com.br/termos_mobile.html');
});

Route::get('static_pages/faq.html', function(){
    return redirect()->away('https://caronae.com.br/faq.html?mobile');
});
