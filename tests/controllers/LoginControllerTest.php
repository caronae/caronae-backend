<?php

namespace Tests;

use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testReturnsInstitutionsView()
    {
        $response = $this->get('login');

        $response->assertStatus(200);
        $response->assertViewIs('login.institutions');
        $response->assertViewHas('institutions');
    }

    public function testReturnsErrorView()
    {
        $errorMessage = 'Error message';
        $response = $this->get('login?error=' . $errorMessage);

        $response->assertStatus(401);
        $response->assertViewIs('login.error');
        $response->assertViewHas('error', $errorMessage);
    }

    public function testReturnsTokenView()
    {
        $user = factory(User::class)->make();
        JWTAuth::shouldReceive('authenticate')->andReturn($user);

        $response = $this->get('login?token=bla');

        $response->assertStatus(200);
        $response->assertViewIs('login.token');
        $response->assertViewHas('user', $user);
    }

}
