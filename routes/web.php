<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication UI preview.
Route::get('/auth-preview', function () {
    return view('ui_forms.login');
});

//for admin ui preview
Route::get('/admin-preview', function () {
    return view('admin_ui.dashboard');
});

Route::get('/ui-preview/{form}', function (string $form) {
    $forms = [
        'change-password',
        'create-post',
        'edit-profile',
        'forgot-password',
        'login',
        'register',
        'report-post',
        'schedule-post',
    ];

    abort_unless(in_array($form, $forms, true), 404);

    return view("ui_forms.{$form}");
})->whereIn('form', [
    'change-password',
    'create-post',
    'edit-profile',
    'forgot-password',
    'login',
    'register',
    'report-post',
    'schedule-post',
]);

Route::get('/create-post', function () {
    return view('welcome', ['hidePosts' => true, 'isScheduling' => false]);
})->name('posts.create');

Route::get('/schedule-post', function () {
    return view('welcome', ['hidePosts' => true, 'isScheduling' => true]);
})->name('posts.schedule');