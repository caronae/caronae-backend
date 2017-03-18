<?php

use Caronae\Http\Middleware\ApiV1Authenticate;
use Caronae\Models\User;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class ApiV1AuthenticateTest extends TestCase
{
    use DatabaseTransactions;

    public function testHandleShouldReturn401WithInvalidToken()
    {
    	$request = Mockery::mock(Request::class);
    	$request->shouldReceive('header')->with('token')->andReturn('xxx');
    	$middleware = new ApiV1Authenticate();
    	$response = $middleware->handle($request, function($r){ });
        $this->assertResponseIs401TokenNotAuthorized($response);
    }

    public function testHandleShouldReturn401WithoutToken()
    {
    	$request = Mockery::mock(Request::class);
    	$request->shouldReceive('header')->with('token')->andReturn(null);
    	$middleware = new ApiV1Authenticate();
    	$response = $middleware->handle($request, function($r){ });
        $this->assertResponseIs401TokenNotAuthorized($response);
    }

    public function testShouldContinueWithValidToken()
    {
    	$user = factory(User::class)->create()->fresh();
    	$request = Mockery::mock(Request::class)->makePartial();
    	$request->shouldReceive('header')->with('token')->andReturn($user->token);
    	$request->shouldReceive('header')->andReturn();
    	$middleware = new ApiV1Authenticate();

    	// Assert that $next($request) will be called
    	$response = $middleware->handle($request, function($r) { 
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
    	$middleware = new ApiV1Authenticate();

    	$response = $middleware->handle($request, function($r) {});
    	$this->assertEquals($user, $request->currentUser);
    }

    private function assertResponseIs401TokenNotAuthorized($response)
    {
    	$this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([
        	'error' => 'User token not authorized.'
        ], (array)$response->getData());
    }
}
