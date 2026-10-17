<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication UI preview.
Route::get('/auth-preview', function () {
    return view('ui_forms.login');
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

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('admin.login');
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
