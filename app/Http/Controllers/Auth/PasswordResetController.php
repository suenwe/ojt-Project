<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function sendCode(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $user = User::query()->where('email', $validated['email'])->first();

        $request->session()->regenerate();
        $emailOtp->issue(
            $validated['email'],
            'password_reset',
            $user ? (int) $user->getKey() : null,
            deliver: $user !== null,
        );

        return redirect()->route('verification-code.challenge')
            ->with('status', 'If an account exists for that address, a verification code has been sent.');
    }

    public function edit(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('verified_password_reset_user_id')) {
            return redirect()->route('forgot-password')->withErrors([
                'email' => 'Verify your email before resetting your password.',
            ]);
        }

        return view('auth.reset-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $userId = $request->session()->pull('verified_password_reset_user_id');
        $user = $userId ? User::query()->find($userId) : null;

        if (! $user) {
            return redirect()->route('forgot-password')->withErrors([
                'email' => 'Verify your email before resetting your password.',
            ]);
        }

        $user->forceFill(['password' => $validated['password']])->save();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Your password has been reset. Sign in with your new password.');
    }
}
