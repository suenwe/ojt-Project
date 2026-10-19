<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        if ($user->banned_at !== null) {
            return back()
                ->withErrors(['email' => 'This account has been suspended.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $emailOtp->issue($user->email, 'login', (int) $user->getKey(), [
            'remember' => $request->boolean('remember'),
        ]);

        return redirect()->route('verification-code.challenge')
            ->with('status', 'A six-digit verification code has been sent to your email.');
    }

    public function register(): View
    {
        return view('ui_forms.register');
    }

    public function storeRegistration(Request $request, EmailOtpService $emailOtp): RedirectResponse
    {
        $registration = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:30'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'birthday' => ['required', 'date', 'before_or_equal:today'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->numbers()->symbols()],
        ]);

        $request->session()->regenerate();
        $emailOtp->issue($registration['email'], 'registration', data: [
            'username' => $registration['username'],
            'phone_number' => $registration['phone_number'] ?? null,
            'birthday' => $registration['birthday'],
            'password' => Hash::make($registration['password']),
        ]);

        return redirect()->route('verification-code.challenge')
            ->with('status', 'A six-digit verification code has been sent to your email.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been signed out.');
    }
}
