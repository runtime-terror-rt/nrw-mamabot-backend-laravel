<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-login', function () {
    return redirect('/admin/login');
})->name('login');


