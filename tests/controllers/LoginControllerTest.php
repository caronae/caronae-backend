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
        $response = $this->call('GET', 'login', [ 'error' => $errorMessage ]);

        $response->assertStatus(401);
        $response->assertViewIs('login.error');
        $response->assertViewHas('error', $errorMessage);
    }

    public function testReturnsTokenView()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);

        $response = $this->call('GET', 'login', [ 'token' => $jwtToken ]);

        $response->assertStatus(200);
        $response->assertViewIs('login.token');
        $response->assertViewHas('user', $user->fresh());
        $response->assertViewHas('token', $jwtToken);
        $response->assertViewHas('displayTermsOfUse', true);
    }

    public function testAcceptsTermsOfUse()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);

        $response = $this->call('GET','login', [
            'token' => $jwtToken,
            'acceptedTermsOfUse' => true
        ]);

        $response->assertStatus(200);
        $response->assertCookie('acceptedTermsOfUse', true);
    }

    public function testTokenViewDoesNotDisplayTermsAgain()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);

        $cookies = [ 'acceptedTermsOfUse' => Crypt::encrypt(true) ];
        $response = $this->call('GET', 'login', [ 'token' => $jwtToken ], $cookies);

        $response->assertViewHas('displayTermsOfUse', false);
    }

    public function testRefreshToken()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);
        $previousToken = $user->token;

        $response = $this->post('refreshToken', [ 'token' => $jwtToken ]);

        $response->assertRedirect(route('chave', [ 'token' => $jwtToken ]));
        $this->assertNotEquals($user->fresh()->token, $previousToken);
    }
}
