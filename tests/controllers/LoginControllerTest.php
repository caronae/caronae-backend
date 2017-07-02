<?php

namespace Tests;

use Caronae\Models\User;
use Crypt;
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
        $this->mockJWT($user);

        $response = $this->get('login?token=bla');

        $response->assertStatus(200);
        $response->assertViewIs('login.token');
        $response->assertViewHas('user', $user);
        $response->assertViewHas('token', 'bla');
        $response->assertViewHas('displayTermsOfUse', true);
    }

    public function testAcceptsTermsOfUse()
    {
        $user = factory(User::class)->make();
        $this->mockJWT($user);

        $response = $this->get('login?token=bla&acceptedTermsOfUse=1');

        $response->assertStatus(200);
        $response->assertCookie('acceptedTermsOfUse', true);
    }

    public function testTokenViewDoesNotDisplayTermsAgain()
    {
        $user = factory(User::class)->make();
        $this->mockJWT($user);

        $cookies = [
            'acceptedTermsOfUse' => Crypt::encrypt(true),
        ];
        $response = $this->call('GET', 'login', [ 'token' => 'bla' ], $cookies);

        $response->assertViewHas('displayTermsOfUse', false);
    }

    private function mockJWT($user)
    {
        JWTAuth::shouldReceive('parseToken')->andReturnSelf();
        JWTAuth::shouldReceive('authenticate')->andReturn($user);
    }
}
