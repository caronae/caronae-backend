<?php

namespace Caronae\Http\Middleware;

use Closure;

class ApiV1AuthenticateRideDriver extends ApiV1Authenticate
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
        if (!$request->currentUser->ownsRide($request->ride)) {
            return response()->json(['error' => 'User is not the driver of the ride.'], 403);
        }

        return $next($request);
    }
}
