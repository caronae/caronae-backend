<?php

namespace Caronae\Http\Controllers\Admin;

use Backpack\Base\app\Http\Controllers\Controller;
use Carbon\Carbon;
use Caronae\Models\Admin;
use Caronae\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class TokenController extends Controller
{
    const CACHE_TIME_MINUTES = 60 * 24 * 365;

    public function index(Request $request)
    {
        $admin = $request->user();
        $caronaeUser = $admin->user;
        $tokens = [];

        if ($caronaeUser) {
            $tokens = collect(cache($this->getTokenCacheKey($admin), []));

            $tokens = $tokens->map(function ($token) {
                try {
                    $payload = JWTAuth::setToken($token)->payload();
                } catch (JWTException $exception) {
                    return null;
                }

                $expirationDate = Carbon::createFromTimestampUTC($payload['exp']);
                $issuedDate = Carbon::createFromTimestampUTC($payload['iat']);

                return [
                    'token' => $token,
                    'expiration' => $expirationDate->toDayDateTimeString(),
                    'issued_at' => $issuedDate->toDayDateTimeString(),
                ];
            })->reject(function ($value) {
                return is_null($value);
            });
        }

        return view('admin.token', [ 'tokens' => $tokens, 'caronaeUser' => $caronaeUser]);
    }

    public function new(Request $request)
    {
        $admin = $request->user();
        $user = $admin->user;
        $generatedTokens = cache($this->getTokenCacheKey($admin), []);
        Log::info('Admin requested new self-service token.');

        $generatedTokens[] = JWTAuth::fromUser($user);
        cache([$this->getTokenCacheKey($admin) => $generatedTokens], self::CACHE_TIME_MINUTES);

        return redirect()->route('self-service-token');
    }

    private function getTokenCacheKey(Admin $admin)
    {
        $user = $admin->user;

        return 'self-service-tokens-user-' . $user->id;
    }
}
