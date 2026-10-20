<?php

use App\Models\User;
use App\Rules\UniqueUsername;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:create', function () {
    $email = strtolower(trim((string) $this->ask('Administrator email address')));

    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
        $this->error('Enter a valid email address of no more than 255 characters.');

        return Command::FAILURE;
    }

    $user = User::query()->where('email', $email)->first();

    if ($user) {
        if ($user->role === 'admin') {
            $this->info($email.' already has administrator access.');

            return Command::SUCCESS;
        }

        if (! $this->confirm('Promote the existing account '.$email.' to administrator?', false)) {
            $this->comment('No account changes were made.');

            return Command::SUCCESS;
        }

        $user->forceFill([
            'role' => 'admin',
            'banned_at' => null,
            'ban_expires_at' => null,
            'ban_reason' => null,
        ])->save();

        $this->info('Administrator access granted to '.$email.'.');

        return Command::SUCCESS;
    }

    $username = trim((string) $this->ask('Administrator username'));

    if ($username === '' || mb_strlen($username) > 255) {
        $this->error('Enter a username between 1 and 255 characters.');

        return Command::FAILURE;
    }

    if (UniqueUsername::isTaken($username)) {
        $this->error('That username is already taken. Choose another one.');

        return Command::FAILURE;
    }

    $password = (string) $this->secret('Password (at least 8 characters, with a number and a symbol)');
    $confirmation = (string) $this->secret('Confirm password');

    $passwordCheck = Validator::make(
        ['password' => $password, 'password_confirmation' => $confirmation],
        ['password' => ['required', 'confirmed', Password::min(8)->numbers()->symbols()]],
    );

    if ($passwordCheck->fails()) {
        $this->error('The password must have at least 8 characters, a number and a symbol, and both entries must match.');
        return Command::FAILURE;
    }

    User::query()->create([
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'role' => 'admin',
    ]);

    $this->info('Administrator account created for '.$email.'.');

    return Command::SUCCESS;
})->purpose('Create an administrator account or promote an existing member');
