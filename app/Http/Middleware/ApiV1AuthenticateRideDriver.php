<?php

namespace Caronae\Http\Middleware;

use Auth;
use Closure;

class ApiV1AuthenticateRideDriver extends ApiV1Authenticate
{
    public function handle($request, Closure $next)
    {
        if (!Auth::user()->ownsRide($request->ride)) {
            return response()->json(['error' => 'User is not the driver of the ride.'], 403);
        }

        return $next($request);
    }
}
