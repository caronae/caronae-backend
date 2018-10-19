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
        $this->authenticateAs($user, $headers);
        $headers['Authorization'] = 'Bearer token';
        return parent::json($method, $uri, $data, $headers);
    }

    public function authenticateAs(User $user)
    {
        JWTAuth::shouldReceive('parseToken->authenticate')->andReturn($user);
        Auth::setUser($user);
    }


}