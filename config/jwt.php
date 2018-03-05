<?php

return [

    'secret' => env('JWT_SECRET', 'changeme'),
    'ttl' => 60,
    'refresh_ttl' => 525600, // 525,600 = 1 year
    'algo' => 'HS256',
    'user' => 'Caronae\Models\User',
    'identifier' => 'id',
    'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti'],
    'blacklist_enabled' => true,
    'providers' => [
        'user' => 'Tymon\JWTAuth\Providers\User\EloquentUserAdapter',
        'jwt' => 'Tymon\JWTAuth\Providers\JWT\NamshiAdapter',
        'auth' => 'Caronae\Providers\JWTUserAuthAdapter',
        'storage' => 'Tymon\JWTAuth\Providers\Storage\IlluminateCacheAdapter',
    ],

];
