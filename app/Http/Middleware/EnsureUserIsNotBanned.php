<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->banned_at !== null, 403, 'This account has been suspended.');

        return $next($request);
    }
}
