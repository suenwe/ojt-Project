<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//for authentication ui preview
Route::get('/auth-preview', function () {
    return view('auth_ui.landing');
});

//for admin ui preview
Route::get('/admin-preview', function () {
    return view('admin_ui.dashboard');
});