<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\UniqueUsername;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    /**
     * Create a new administrator account from the dashboard.
     */
    public function storeAdmin(Request $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $validated = $request->validateWithBag('createAdmin', [
            'username' => ['required', 'string', 'min:3', 'max:30', new UniqueUsername],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->numbers()->symbols()],
        ]);

        $newAdmin = User::query()->forceCreate([
            'username' => trim($validated['username']),
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'admin',
        ]);

        return to_route('admin.dashboard', ['section' => 'users'])
            ->with('status', 'Admin account created for '.$newAdmin->username.'. They can sign in now and change their password in Profile settings.');
    }

    public function updateBan(Request $request, User $user): RedirectResponse
    {
        if ($user->role === 'admin' || $user->is($request->user())) {
            throw ValidationException::withMessages([
                'user' => 'Admin accounts and your own account cannot be banned here.',
            ]);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:ban,unban'],
            'ban_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['action'] === 'ban') {
            $user->forceFill([
                'banned_at' => $user->banned_at ?? now(),
                'ban_expires_at' => null,
                'ban_reason' => $validated['ban_reason'] ?: 'Banned by an administrator.',
            ])->save();
        } else {
            $user->forceFill([
                'banned_at' => null,
                'ban_expires_at' => null,
                'ban_reason' => null,
            ])->save();
        }

        return to_route('admin.dashboard', ['section' => 'users'])
            ->with('status', $validated['action'] === 'ban' ? 'User banned.' : 'User unbanned.');
    }
}
