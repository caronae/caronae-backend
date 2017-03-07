<?php

namespace Caronae\Http\Middleware;

use Closure;

class ApiV1AuthenticateRequestedUser extends ApiV1Authenticate
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
        if ($request->user != $request->currentUser) {
            return response()->json(['error' => 'You are not authorized.'], 403);
        }

        return $next($request);
    }
}
