<?php

namespace App\Http\Middleware;

use Closure;

class ApiV1AuthenticateRideUser extends ApiV1Authenticate
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
        if (!$request->user->belongsToRide($request->ride)) {
            return response()->json(['error' => 'User does not belong to ride.'], 403);
        }

        return $next($request);
    }
}
