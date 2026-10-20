<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    /**
     * Admins sign in on the shared login page; send them to the dashboard afterwards.
     */
    public function create(Request $request): RedirectResponse
    {
        if (! $request->session()->has('url.intended')) {
            $request->session()->put('url.intended', route('admin.dashboard'));
        }

        return to_route('login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['role' => 'admin', 'banned_at' => null], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These credentials do not match an active administrator account.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('status', 'You have been signed out.');
    }
}