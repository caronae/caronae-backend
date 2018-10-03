<?php

namespace Tests;

use Auth;
use Caronae\Http\Middleware\ApiV1AuthenticateRequestedUser;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class ApiV1AuthenticateRequestedUserTest extends TestCase
{
    use DatabaseTransactions;
    private $middleware;
    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->middleware = new ApiV1AuthenticateRequestedUser();
        $this->user = factory(User::class)->create()->fresh();
        Auth::setUser($this->user);
    }

    /** @test */
    public function should_continue_if_request_is_for_current_user()
    {
        $request = new Request();
        $request->user = $this->user;

        $response = $this->middleware->handle($request, function() { return 'next'; });
        $this->assertEquals('next', $response);
    }

    /** @test */
    public function should_return_403_if_request_is_for_other_user()
    {
        $user2 = factory(User::class)->create()->fresh();
        $request = new Request();
        $request->user = $user2;

        $response = $this->middleware->handle($request, function() { return 'next'; });
        $this->assertEquals(403, $response->getStatusCode());
    }
}
