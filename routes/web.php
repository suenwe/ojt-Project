<?php

use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// for authentication ui preview
Route::get('/auth-preview', function () {
    return view('auth_ui.landing');
});

// for admin ui preview
Route::get('/admin-preview', function () {
    return view('admin_ui.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/register', [LoginController::class, 'register'])->name('register');
    Route::post('/register', [LoginController::class, 'storeRegistration'])
        ->middleware('throttle:3,1')
        ->name('register.store');

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('forgot-password');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])
        ->middleware('throttle:3,1')
        ->name('password.email');

    Route::get('/reset-password', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:3,1')
        ->name('password.update');
});

Route::get('/verify-email-code', [EmailOtpController::class, 'create'])->name('verification-code.challenge');
Route::post('/verify-email-code', [EmailOtpController::class, 'verify'])
    ->middleware('throttle:6,1')
    ->name('verification-code.verify');
Route::post('/verify-email-code/resend', [EmailOtpController::class, 'resend'])
    ->middleware('throttle:3,1')
    ->name('verification-code.resend');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
