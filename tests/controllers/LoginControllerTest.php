<?php

namespace Tests;

use Caronae\Models\Institution;
use Caronae\Models\User;
use Crypt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function shouldReturnInstitutionsViewWhenThereAreManyInstitutions()
    {
        factory(Institution::class, 2)->create();
        $response = $this->get('login');

        $response->assertStatus(200);
        $response->assertViewIs('login.institutions');
        $response->assertViewHas('institutions');
    }

    /** @test */
    public function shouldRedirectToInstitutionWhenThereIsOneInstitution()
    {
        $institution = factory(Institution::class)->create();
        $response = $this->get('login');

        $response->assertRedirect($institution->authentication_url);
    }

    /** @test */
    public function shouldReturnErrorView()
    {
        $errorMessage = 'Error message';
        $response = $this->call('GET', 'login', [ 'error' => $errorMessage ]);

        $response->assertStatus(401);
        $response->assertViewIs('login.error');
        $response->assertViewHas('error', $errorMessage);
    }

    /** @test */
    public function shouldRememberLoginType()
    {
        $response = $this->call('GET', 'login', [ 'type' => 'app' ]);

        $response->assertSessionHas('login_type', 'app');
    }

    /** @test */
    public function shouldRememberOnlyMostRecentLoginType()
    {
        $response = $this->withSession([ 'type' => 'app' ])->call('GET', 'login', [ 'type' => 'web' ]);

        $response->assertSessionHas('login_type', 'web');
    }

    /** @test */
    public function shouldDefaultToWebLoginTypeWhenNotSpecified()
    {
        $response = $this->call('GET', 'login');

        $response->assertSessionHas('login_type', 'web');
    }

    /** @test */
    public function shouldReturnTokenViewWithWebType()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);

        $response = $this->withSession([ 'type' => 'web' ])->call('GET', 'login', [ 'token' => $jwtToken ]);

        $response->assertStatus(200);
        $response->assertViewIs('login.token');
        $response->assertViewHas('user', $user->fresh());
        $response->assertViewHas('token', $jwtToken);
        $response->assertViewHas('displayTermsOfUse', true);
    }

    /** @test */
    public function shouldRedirectToAppWithMobileType()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);

        $response = $this->withSession([ 'type' => 'app' ])->call('GET', 'login', [ 'token' => $jwtToken ]);

        $response->assertRedirect('caronae://login?id=' . $user->id . '&id_ufrj=' . $user->id_ufrj . '&token=' . $user->token);
    }

    /** @test */
    public function shouldSaveCookieWhenAcceptingTermsOfUse()
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

    /** @test */
    public function shouldNotDisplayTermsAgainIfHasAcceptedTerms()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);

        $cookies = [ 'acceptedTermsOfUse' => Crypt::encrypt(true) ];
        $response = $this->call('GET', 'login', [ 'token' => $jwtToken ], $cookies);

        $response->assertViewHas('displayTermsOfUse', false);
    }

    /** @test */
    public function shouldRefreshToken()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);
        $previousToken = $user->token;

        $response = $this->post('refreshToken', [ 'token' => $jwtToken ]);

        $response->assertRedirect(route('chave', [ 'token' => $jwtToken ]));
        $this->assertNotEquals($user->fresh()->token, $previousToken);
    }
}
