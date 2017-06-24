<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$this->get('chave', 'LoginController@index')->name('chave');



// Public site interface
Route::get('/', function() {
    return redirect()->route('home');
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
