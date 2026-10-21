<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\UniqueUsername;
use App\Services\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailOtpController extends Controller
{
    public function create(EmailOtpService $emailOtp): View|RedirectResponse
    {
        $challenge = $emailOtp->current();

        if ($challenge === null) {
            return redirect()->route('login')->withErrors([
                'email' => 'Start again to request a verification code.',
            ]);
        }

        return view('auth.verify-email-code', [
            'email' => $challenge['email'],
        ]);
    }

    public function verify(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);
        $challenge = $emailOtp->current();

        if ($challenge === null) {
            return redirect()->route('login')->withErrors([
                'email' => 'Start again to request a verification code.',
            ]);
        }

        if ($emailOtp->hasExpired()) {
            $emailOtp->invalidateCode();

            return back()->withErrors([
                'code' => 'That code has expired. Request a new code to continue.',
            ]);
        }

        if (! $emailOtp->verify($validated['code'])) {
            return back()->withErrors(['code' => 'That verification code is incorrect.']);
        }

        $user = $challenge['user_id'] === null
            ? null
            : User::query()->find($challenge['user_id']);

        if ($challenge['purpose'] === 'registration') {
            if (User::query()->where('email', $challenge['email'])->exists()) {
                $emailOtp->clear();

                return redirect()->route('register')->withErrors([
                    'email' => 'An account with this email already exists. Sign in instead.',
                ]);
            }

            if (UniqueUsername::isTaken($challenge['data']['username'])) {
                $emailOtp->clear();

                return redirect()->route('register')->withErrors([
                    'username' => 'This username was taken while you were verifying. Please choose another one.',
                ]);
            }

            $user = User::query()->create([
                'username' => $challenge['data']['username'],
                'email' => $challenge['email'],
                'phone_number' => $challenge['data']['phone_number'] ?? null,
                'birthday' => $challenge['data']['birthday'],
                'password' => $challenge['data']['password'],
                'role' => 'user',
            ]);

            $emailOtp->clear();
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        if ($challenge['purpose'] === 'password_reset' && $user) {
            $emailOtp->clear();
            $request->session()->regenerate();
            $request->session()->put('verified_password_reset_user_id', $user->getKey());

            return redirect()->route('otp.password.reset');
        }

        $emailOtp->clear();

        return redirect()->route('password.request')->with('status', 'If an account exists for that address, a verification code has been sent.');
    }

    public function resend(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $challenge = $emailOtp->current();

        if ($challenge === null) {
            return redirect()->route('login')->withErrors([
                'email' => 'Start again to request a verification code.',
            ]);
        }

        if ($challenge['purpose'] === 'password_reset' && $challenge['user_id'] === null) {
            return back()->with('status', 'If an account exists for that address, a verification code has been sent.');
        }

        if ($challenge['purpose'] === 'registration'
            && User::query()->where('email', $challenge['email'])->exists()) {
            $emailOtp->clear();

            return redirect()->route('register')->withErrors([
                'email' => 'An account with this email already exists. Sign in instead.',
            ]);
        }

        if ($challenge['user_id'] !== null) {
            $user = User::query()->find($challenge['user_id']);

            $user?->clearExpiredBan();

            if (! $user || $user->isBanned()) {
                $emailOtp->clear();

                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'This account is no longer available. Start again.']);
            }
        }

        $emailOtp->issue(
            $challenge['email'],
            $challenge['purpose'],
            $challenge['user_id'],
            $challenge['data'],
        );

        return redirect()->route('verification-code.challenge')
            ->with('status', 'A new verification code has been sent to your email.');
    }
}
