<?php

namespace Caronae\Http\Middleware;

use Auth;
use Closure;

class ApiV1AuthenticateRequestedUser extends ApiV1Authenticate
{
    public function handle($request, Closure $next)
    {
        if ($request->user != Auth::user()) {
            return response()->json(['error' => 'You are not authorized.'], 403);
        }

        return $next($request);
    }
}
