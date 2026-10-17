<?php

namespace App\Http\Middleware;

use App\Models\UserAccessLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CaptureUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $ipAddress = $request->ip();

        if ($user && $ipAddress) {
            $userAgent = (string) $request->userAgent();
            $browser = $this->browserName($userAgent);

            if ($user->getAttribute('IP_address') !== $ipAddress || $user->browser !== $browser) {
                $user->forceFill([
                    'IP_address' => $ipAddress,
                    'browser' => $browser,
                ])->saveQuietly();
            }

            UserAccessLog::query()->create([
                'user_id' => $user->user_id,
                'ip_address' => $ipAddress,
                'browser' => $browser,
                'method' => $request->method(),
                'path' => Str::limit($request->path(), 1024, ''),
                'route_name' => $request->route()?->getName(),
                'accessed_at' => now(),
            ]);
        }

        return $next($request);
    }

    private function browserName(string $userAgent): string
    {
        $browsers = [
            '/Edg(?:e|A|iOS)?\/([\d.]+)/i' => 'Microsoft Edge',
            '/OPR\/([\d.]+)/i' => 'Opera',
            '/Firefox\/([\d.]+)/i' => 'Firefox',
            '/Chrome\/([\d.]+)/i' => 'Chrome',
            '/Version\/([\d.]+).*Safari/i' => 'Safari',
        ];

        foreach ($browsers as $pattern => $name) {
            if (preg_match($pattern, $userAgent, $matches) === 1) {
                return Str::limit($name.' '.$matches[1], 255, '');
            }
        }

        return $userAgent === '' ? 'Unknown' : Str::limit($userAgent, 255, '');
    }
}
