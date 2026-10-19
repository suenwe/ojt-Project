<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ScheduledPostController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', [PostController::class, 'index'])->name('home');

    Route::get('/create-post', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/media/{media}', [PostController::class, 'media'])
        ->whereNumber('media')
        ->name('media.show');

    Route::get('/schedule-post', [ScheduledPostController::class, 'index'])->name('posts.schedule');
    Route::post('/schedule-post', [ScheduledPostController::class, 'store'])->name('schedule-post.store');
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
    Route::post('/report-post', function () {
        return redirect()->route('home')->with('success', 'Thank you. Your report has been submitted to the moderators.');
    })->name('report.store');

    Route::get('/profile', function () {
        return view('welcome', ['showProfileSettings' => true]);
    })->name('profile.edit');
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

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::get('/register', [LoginController::class, 'register'])->name('register');
    Route::post('/register', [LoginController::class, 'storeRegistration'])
        ->middleware('throttle:3,1')
        ->name('register.store');

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])
        ->middleware('throttle:3,1')
        ->name('password.email');

    Route::get('/reset-password', [PasswordResetController::class, 'edit'])->name('otp.password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:3,1')
        ->name('password.update');

    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
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

Route::middleware('guest')->group(function (): void {
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
