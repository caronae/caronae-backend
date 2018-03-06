<?php

namespace Tests;

use Auth;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function jsonAs(User $user, $method, $uri, array $data = [], array $headers = [])
    {
        JWTAuth::shouldReceive('parseToken->authenticate')->andReturn($user);
        $headers['Authorization'] = 'Bearer token';
        Auth::setUser($user);
        return parent::json($method, $uri, $data, $headers);
    }


}