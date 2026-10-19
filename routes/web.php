<?php

use App\Http\Controllers\ScheduledPostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/', function () {
    return view('welcome');

});
Route::get('/create-post', [PostController::class, 'index'])
    ->name('posts.index');
Route::get('/schedule-post', [ScheduledPostController::class, 'index'])
    ->name('schedule-post');
Route::post('/schedule-post', [ScheduledPostController::class, 'store'])
    ->name('schedule-post.store');
Route::delete('/schedule-post/{post}', [ScheduledPostController::class, 'destroy'])
    ->whereNumber('post')
    ->name('schedule-post.destroy');
Route::get('/post-media/{media}', [ScheduledPostController::class, 'media'])
    ->whereNumber('media')
    ->name('post-media.show');
Route::post('/posts', [PostController::class, 'store'])
    ->name('posts.store');

Route::get('/media/{media}', [PostController::class, 'media'])
    ->whereNumber('media')
    ->name('media.show');