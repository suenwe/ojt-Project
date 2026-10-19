<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
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
