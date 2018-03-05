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
        if (!empty($request->header('token'))) {
            return $this->handleLegacyTokenAuthentication($request, $next);
        }

        if (!empty($request->header('Authorization'))) {
            return $this->handleJWTAuthentication($request, $next);
        }

        return response()->json(['error' => 'Authentication required.'], 401);
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

    private function handleLegacyTokenAuthentication($request, Closure $next)
    {
        if (($user = User::where('token', $request->header('token'))->first()) == NULL || $user->banned) {
            return response()->json(['error' => 'User token not authorized.'], 401);
        }

        $request->merge([
            'currentUser' => $user
        ]);

        auth()->setUser($user);

        $this->updateUserAppInfo($request);

        return $next($request);
    }

    private function handleJWTAuthentication($request, Closure $next)
    {
        $response = $next($request);

        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            try {
                $refreshed = JWTAuth::refresh(JWTAuth::getToken());
                $user = JWTAuth::setToken($refreshed)->toUser();
                if ($user->banned) {
                    return response()->json(['error' => 'User is banned.'], 401);
                }

                $response->header('Authorization', "Bearer $refreshed");
            } catch (JWTException $e) {
                return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
            }
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid.', JWTAuth::parseToken()], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['error' => 'Error validating token.', 'exception' => $e->getMessage()], $e->getStatusCode());
        }

        $request->merge([
            'currentUser' => $user
        ]);
        auth()->setUser($user);

        $this->updateUserAppInfo($request);
        return $response;
    }
}
