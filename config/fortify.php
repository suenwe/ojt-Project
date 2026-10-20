<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => true,
    'home' => '/',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'limiters' => [
        'login' => 'login',
        'passkeys' => 'passkeys',
    ],
    'views' => true,
    'passkeys' => [
        'relying_party_id' => parse_url(config('app.url'), PHP_URL_HOST),
        'allowed_origins' => [config('app.url')],
        'timeout' => 60000,
    ],
    'features' => [
        // Registration and password reset use the email-code flow in
        // App\Http\Controllers\Auth (Implement2FA), so Fortify's versions are off.
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::passkeys([
            'confirmPassword' => true,
        ]),
    ],
];
