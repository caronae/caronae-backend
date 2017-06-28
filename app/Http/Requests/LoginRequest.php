<?php

namespace Caronae\Http\Requests;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginRequest extends Request
{
    public function hasSelectedInstitution()
    {
        return $this->has('token') || $this->has('error');
    }

    public function authenticateUser()
    {
        $user = JWTAuth::authenticate();
        if (!$user) throw new JWTException('User not found', 401);
        return $user;
    }
}