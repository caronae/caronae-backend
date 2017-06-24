<?php

namespace Caronae\Providers;

use Illuminate\Auth\AuthManager;
use Tymon\JWTAuth\Providers\Auth\IlluminateAuthAdapter;

class JWTUserAuthAdapter extends IlluminateAuthAdapter
{
    public function __construct(AuthManager $auth)
    {
        parent::__construct($auth);
        $this->auth->shouldUse('api');
    }
}