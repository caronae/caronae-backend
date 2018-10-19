<?php

namespace Caronae\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateUserAppInfo
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guest()) {
            return $next($request);
        }

        $userAgent = $request->header('User-Agent');
        $appVersionRegex = '(\d+\.)?(\d+\.)?(\*|\d+)';
        $user = Auth::user();

        if (preg_match('/Caronae.*\/(?P<version>' . $appVersionRegex . ') .*(?P<platform>(iOS|Android))/', $userAgent, $matches)) {
            $platform = $matches['platform'];
            $version = $matches['version'];

            if ($platform == 'iOS' || $platform == 'Android') {
                $user->app_platform = $platform;
                $user->app_version = $version;
                $user->save();
            }
        }

        return $next($request);
    }
}
