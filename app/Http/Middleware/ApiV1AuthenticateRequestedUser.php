<?php

namespace Caronae\Http\Middleware;

use Auth;
use Closure;
use Log;

class ApiV1AuthenticateRequestedUser extends ApiV1Authenticate
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($request->user != $user) {
            Log::info('Request denied with a 403 because the requested user does not match the authenticated user.', [
                'path' => $request->path(),
                'requested_user' => $request->user->id,
                'authenticated_user' => $user->id,
            ]);
            return response()->json(['error' => 'You are not authorized.'], 403);
        }

        return $next($request);
    }
}
