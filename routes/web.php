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

$this->get('chave', 'ChaveController@index');

// Authentication 
$this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
$this->post('login', 'Auth\LoginController@login');
$this->post('logout', 'Auth\LoginController@logout')->name('logout');
$this->get('logout', 'Auth\LoginController@logout');

// Password Reset

$this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
$this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
$this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
$this->post('password/reset', 'Auth\ResetPasswordController@reset');

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
