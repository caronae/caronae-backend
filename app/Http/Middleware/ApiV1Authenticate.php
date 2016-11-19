<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

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
        if (empty($request->header('token')) || ($user = User::where('token', $request->header('token'))->first()) == NULL) {
            return response()->json(['error'=>'User token not authorized.'], 403);
        }

        $request->user = $user;
        return $next($request);
    }
}
