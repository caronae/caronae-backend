<?php

namespace Caronae\Http\Middleware;

use Auth;
use Closure;

class ApiV1AuthenticateRideUser extends ApiV1Authenticate
{
    public function handle($request, Closure $next)
    {
        if (!Auth::user()->belongsToRide($request->ride)) {
            return response()->json(['error' => 'User does not belong to ride.'], 403);
        }

        return $next($request);
    }
}
