<?php

namespace Caronae\Http\Middleware;

use Closure;
use Caronae\Models\User;
use Illuminate\Support\Facades\Log;

class ApiV1Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty($request->header('token')) || ($user = User::where('token', $request->header('token'))->first()) == NULL || $user->banned) {
            return response()->json(['error' => 'User token not authorized.'], 401);
        }

        $request->currentUser = $user;

        $this->updateUserAppInfo($request);

        return $next($request);
    }

    private function updateUserAppInfo($request)
    {
        $userAgent = $request->header('User-Agent');
        $appVersionRegex = '(\d+\.)?(\d+\.)?(\*|\d+)';

        if (preg_match('/Caronae\/(?P<version>' . $appVersionRegex . ') .*(?P<platform>(iOS|Android))/', $userAgent, $matches)) {
            $platform = $matches['platform'];
            $version = $matches['version'];

            if ($platform == 'iOS' || $platform == 'Android') {
                $request->currentUser->app_platform = $platform;
                $request->currentUser->app_version = $version;
                $request->currentUser->save();
            }
        }
    }
}
