<?php

namespace App\Http\Controllers;

use App\Services\MemberAccountRemover;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DeleteAccountController extends Controller
{
    /**
     * Let a member permanently delete their own account after confirming their password.
     */
    public function __invoke(Request $request, MemberAccountRemover $remover): RedirectResponse
    {
        $member = $request->user();

        Gate::authorize('deleteOwnAccount', $member);

        $request->validateWithBag('deleteAccount', [
            'password' => ['required', 'string', 'current_password:web'],
        ], [
            'password.current_password' => 'The password is incorrect.',
        ]);

        Auth::logout();
        $remover->delete($member);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('status', 'Your account has been deleted.');
    }
}
