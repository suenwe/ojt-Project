<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ScheduledPostController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    // 1. Change this to use the controller so we can pass posts to the welcome page
    Route::get('/', [PostController::class, 'index'])->name('home');

    Route::get('/create-post', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])
        ->name('posts.store');

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
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
        Route::get('/report-post', function (\Illuminate\Http\Request $request) { 
        return view('ui_forms.report-post', ['postId' => $request->query('post_id')]); 
    })->name('report.post');

    // 2. POST route: Handles the form submission when they click "Submit Report"
    Route::post('/report-post', function (\Illuminate\Http\Request $request) {
        // You would normally save the report to the database here.
        // For now, we redirect back to the home page with a success message.
        return redirect()->route('home')->with('success', 'Thank you. Your report has been submitted to the moderators.');
    })->name('report.store');
    Route::get('/profile', function () {
        return view('welcome', ['showProfileSettings' => true]);
    })->name('profile.edit');
});

Route::get('/media/{media}', [PostController::class, 'media'])
    ->whereNumber('media')
    ->name('media.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('admin.login.store');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

        Route::patch('/reports/{report}', [AdminReportController::class, 'review'])
            ->name('reports.review');

        Route::patch('/users/{user}/ban', [AdminUserController::class, 'updateBan'])
            ->name('users.ban');

        Route::post('/categories', [AdminSettingsController::class, 'storeCategory'])
            ->name('categories.store');
        Route::put('/categories/{category}', [AdminSettingsController::class, 'updateCategory'])
            ->name('categories.update');
        Route::delete('/categories/{category}', [AdminSettingsController::class, 'destroyCategory'])
            ->name('categories.destroy');

        Route::post('/restricted-terms', [AdminSettingsController::class, 'storeKeyword'])
            ->name('keywords.store');
        Route::delete('/restricted-terms/{keyword}', [AdminSettingsController::class, 'destroyKeyword'])
            ->name('keywords.destroy');

        Route::post('/notifications', [AdminSettingsController::class, 'storeNotification'])
            ->name('notifications.store');
        Route::put('/notifications/{notification}', [AdminSettingsController::class, 'updateNotification'])
            ->name('notifications.update');
        Route::delete('/notifications/{notification}', [AdminSettingsController::class, 'destroyNotification'])
            ->name('notifications.destroy');
    });
