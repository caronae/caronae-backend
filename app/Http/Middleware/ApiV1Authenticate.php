<?php

namespace Caronae\Http\Middleware;

use Caronae\Models\User;
use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class ApiV1Authenticate
{
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            return $next($request);
        }
        
        if (!empty($request->header('token'))) {
            return $this->handleLegacyTokenAuthentication($request, $next);
        }

        if (!empty($request->header('Authorization'))) {
            return $this->handleJWTAuthentication($request, $next);
        }

        return response()->json(['error' => 'Authentication required.'], 401);
    }

    /** @deprecated */
    private function handleLegacyTokenAuthentication($request, Closure $next)
    {
        if (($user = User::where('token', $request->header('token'))->first()) == null || $user->banned) {
            return response()->json(['error' => 'User token not authorized.'], 401);
        }

        auth()->setUser($user);

        return $next($request);
    }

    private function handleJWTAuthentication($request, Closure $next)
    {
        $response = null;
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            try {
                $refreshed = JWTAuth::refresh();
                $user = JWTAuth::setToken($refreshed)->toUser();
                if ($user->banned) {
                    return response()->json(['error' => 'User is banned.'], 401);
                }

                $response = $next($request);
                $response->header('Authorization', "Bearer $refreshed");
            } catch (JWTException $e) {
                return response()->json(['error' => $e->getMessage()], 401);
            }
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid.'], 400);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error validating token.', 'exception' => $e->getMessage()], 500);
        }

        return ($response != null) ? $response : $next($request);
    }
}
