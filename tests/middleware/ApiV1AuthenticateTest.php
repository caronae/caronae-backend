<?php

namespace Tests;

use Caronae\Http\Middleware\ApiV1Authenticate;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\HeaderBag;

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
        $request = $this->unauthorizedRequest('xxx');

        $response = $this->middleware->handle($request, function($r){ });

        $this->assertResponseIs401($response);
    }

    public function testHandleShouldReturn401WithoutToken()
    {
        $request = $this->unauthorizedRequest(null);

        $response = $this->middleware->handle($request, function($r){ });

        $this->assertResponseIs401($response);
    }

    public function testShouldContinueWithValidToken()
    {
        $request = $this->authenticatedRequest();

        $result = $this->middleware->handle($request, function() {
            return 'next';
        });

        $this->assertEquals($result, 'next');
    }

    public function testShouldSetCurrentUserInRequest()
    {
        $request = $this->authenticatedRequest();

        $result = $this->middleware->handle($request, function($request) {
            return $request;
        });

        $this->assertNotNull($result->currentUser);
    }

    private function assertResponseIs401($response)
    {
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertArrayHasKey('error', (array)$response->getData());
    }

    private function authenticatedRequest(): Request
    {
        $user = factory(User::class)->create()->fresh();
        $request = new Request();
        $request->headers = new HeaderBag(['token' => $user->token]);
        return $request;
    }

    private function unauthorizedRequest($token)
    {
        $request = new Request();
        $request->headers = new HeaderBag(['token' => $token]);
        return $request;
    }
}
