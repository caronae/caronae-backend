<?php

namespace Tests;

use Caronae\Http\Middleware\ApiV1Authenticate;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Mockery;

class ApiV1AuthenticateTest extends TestCase
{
    use DatabaseTransactions;
    private $middleware;

    public function setUp()
    {
        parent::setUp();
        $this->middleware = new ApiV1Authenticate();
    }

    public function testHandleShouldReturn401WithInvalidToken()
    {
    	$request = Mockery::mock(Request::class);
    	$request->shouldReceive('header')->with('token')->andReturn('xxx');
    	$response = $this->middleware->handle($request, function($r){ });
        $this->assertResponseIs401TokenNotAuthorized($response);
    }

    public function testHandleShouldReturn401WithoutToken()
    {
    	$request = Mockery::mock(Request::class);
    	$request->shouldReceive('header')->with('token')->andReturn(null);

    	$response = $this->middleware->handle($request, function($r){ });

        $this->assertResponseIs401TokenNotAuthorized($response);
    }

    public function testShouldContinueWithValidToken()
    {
    	$user = factory(User::class)->create()->fresh();

    	$request = Mockery::mock(Request::class)->makePartial();
    	$request->shouldReceive('header')->with('token')->andReturn($user->token);
    	$request->shouldReceive('header')->andReturn();
    	$request->shouldReceive('merge')->andReturn();

    	// Assert that $next($request) will be called
    	$response = $this->middleware->handle($request, function() {
    		return 'next';
    	});
    	$this->assertEquals($response, 'next');
    }

    public function testShouldSetCurrentUserInRequest()
    {
    	$user = factory(User::class)->create()->fresh();
    	$request = Mockery::mock(Request::class)->makePartial();
    	$request->shouldReceive('header')->with('token')->andReturn($user->token);
    	$request->shouldReceive('header')->andReturn();
    	$request->shouldReceive('merge')->andReturn();

    	$this->middleware->handle($request, function() {});
    	$request->shouldHaveReceived('merge', [['currentUser' => $user]]);
    }

    private function assertResponseIs401TokenNotAuthorized($response)
    {
    	$this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([
        	'error' => 'User token not authorized.'
        ], (array)$response->getData());
    }
}
