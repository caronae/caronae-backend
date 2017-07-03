<?php

$this->get('login', 'LoginController@index')->name('chave');
$this->post('refreshToken', 'LoginController@refreshToken')->name('refreshToken');

Route::get('/', function() {
    return '';
});


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
