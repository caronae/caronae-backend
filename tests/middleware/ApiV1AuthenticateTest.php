<?php

namespace Tests;

use Auth;
use Caronae\Http\Middleware\ApiV1Authenticate;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JWTAuth;
use Symfony\Component\HttpFoundation\HeaderBag;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class ApiV1AuthenticateTest extends TestCase
{
    use DatabaseTransactions;
    private $middleware;

    public function setUp()
    {
        parent::setUp();
        $this->middleware = new ApiV1Authenticate();
    }

    /** @test */
    public function should_return_401_with_invalid_token()
    {
        $request = $this->unauthorizedLegacyRequest('xxx');

        $response = $this->middleware->handle($request, function($r){ });

        $this->assertResponseIsUnauthorized($response);
    }

    /** @test */
    public function should_return_401_without_token()
    {
        $request = $this->unauthorizedLegacyRequest(null);

        $response = $this->middleware->handle($request, function($r){ });

        $this->assertResponseIsUnauthorized($response);
    }

    /** @test */
    public function should_continue_with_valid_legacy_token()
    {
        $request = $this->authenticatedLegacyRequest();

        $result = $this->middleware->handle($request, function() {
            return 'next';
        });

        $this->assertEquals($result, 'next');
    }

    /** @test */
    public function should_continue_with_valid_jwt_token()
    {
        $user = factory(User::class)->create()->fresh();
        $token = JWTAuth::fromUser($user);
        JWTAuth::shouldReceive('parseToken->authenticate')->andReturn($user);

        $request = new Request();
        $request->headers = new HeaderBag(['Authorization' => "Bearer $token"]);

        $result = $this->middleware->handle($request, function() {
            return 'next';
        });

        $this->assertEquals($result, 'next');
    }

    /** @test */
    public function should_return_401_when_token_is_expired_and_cannot_be_refreshed()
    {
        JWTAuth::shouldReceive('parseToken->authenticate')->andThrow(new TokenExpiredException());
        JWTAuth::shouldReceive('getToken')->andReturn('oldtoken');
        JWTAuth::shouldReceive('refresh')->andThrow(new TokenExpiredException('Token expired'));
        $request = $this->jwtRequest();

        $response = $this->middleware->handle($request, function($r){ });

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertArraySubset(['error' => 'Token expired'], (array)$response->getData());
    }

    /** @test */
    public function should_refresh_and_authorize_expired_token()
    {
        $user = factory(User::class)->create()->fresh();
        JWTAuth::shouldReceive('parseToken->authenticate')->andThrow(new TokenExpiredException());
        JWTAuth::shouldReceive('getToken')->andReturn('oldtoken');
        JWTAuth::shouldReceive('refresh')->andReturn('newtoken');
        JWTAuth::shouldReceive('setToken->toUser')->andReturn($user);

        $request = $this->jwtRequest();

        $response = $this->middleware->handle($request, function() {
            return new Response();
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Bearer newtoken', $response->headers->get('Authorization'));
    }

    /** @test */
    public function should_return_401_when_user_is_banned_and_token_expired()
    {
        $user = factory(User::class)->create(['banned' => true])->fresh();
        JWTAuth::shouldReceive('parseToken->authenticate')->andThrow(new TokenExpiredException());
        JWTAuth::shouldReceive('getToken')->andReturn('oldtoken');
        JWTAuth::shouldReceive('refresh')->andReturn('newtoken');
        JWTAuth::shouldReceive('setToken->toUser')->andReturn($user);

        $request = $this->jwtRequest();

        $response = $this->middleware->handle($request, function($r){ return new Response(); });

        $this->assertResponseIsUnauthorized($response);
    }

    /** @test */
    public function should_return_400_when_token_is_invalid()
    {
        JWTAuth::shouldReceive('parseToken->authenticate')->andThrow(new TokenInvalidException());
        $request = $this->jwtRequest();

        $response = $this->middleware->handle($request, function($r){ });

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArraySubset([ 'error' => 'Token is invalid.' ], (array)$response->getData());
    }

    /** @test */
    public function should_return_500_when_jwt_throws_error()
    {
        JWTAuth::shouldReceive('parseToken->authenticate')->andThrow(new JWTException());

        $request = $this->jwtRequest();

        $response = $this->middleware->handle($request, function($r){ });

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertArraySubset([ 'error' => 'Error validating token.' ], (array)$response->getData());
    }

    /** @test */
    public function should_set_authenticated_user()
    {
        $request = $this->authenticatedLegacyRequest();

        $this->middleware->handle($request, function($request) {});

        $this->assertNotNull(Auth::user());
    }

    private function assertResponseIsUnauthorized($response)
    {
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertArrayHasKey('error', (array)$response->getData());
        $this->assertNull($response->headers->get('Authorization'));
    }

    private function authenticatedLegacyRequest()
    {
        $user = factory(User::class)->create()->fresh();
        $request = new Request();
        $request->headers = new HeaderBag(['token' => $user->token]);
        return $request;
    }

    private function unauthorizedLegacyRequest($token)
    {
        $request = new Request();
        $request->headers = new HeaderBag(['token' => $token]);
        return $request;
    }

    /**
     * @return Request
     */
    private function jwtRequest(): Request
    {
        $request = new Request();
        $request->headers = new HeaderBag(['Authorization' => "Bearer token"]);
        return $request;
    }
}
