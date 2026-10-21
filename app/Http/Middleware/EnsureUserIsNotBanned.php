<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    /**
     * Block suspended members from every page, but always let them sign out
     * (the suspended page's Sign out button would otherwise be blocked too).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('logout', 'admin.logout')) {
            return $next($request);
        }

        $user = $request->user();
        $user?->clearExpiredBan();

        abort_if($user?->isBanned(), 403, 'This account has been suspended.');

        return $next($request);
    }
}
