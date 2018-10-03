<?php

namespace Tests;

use Caronae\Http\Controllers\Web\LoginController;
use Caronae\Models\Institution;
use Caronae\Models\User;
use Crypt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function should_return_institutions_view_when_there_are_many_institutions()
    {
        factory(Institution::class, 2)->create();
        $response = $this->get('login');

        $response->assertStatus(200);
        $response->assertViewIs('login.institutions');
        $response->assertViewHas('institutions');
    }

    /** @test */
    public function should_redirect_to_institution_when_there_is_one_institution()
    {
        $institution = factory(Institution::class)->create();
        $response = $this->get('login');

        $response->assertRedirect(route('institution-login', $institution->getRouteKey()));
    }

    /** @test */
    public function shouldShowInstitutionPageWithDetails()
    {
        $institution = factory(Institution::class)->create();
        $response = $this->get('login/' . $institution->getRouteKey());

        $response->assertStatus(200);
        $response->assertViewIs('login.institution');
        $response->assertViewHas('name', $institution->name);
        $response->assertViewHas('authentication_url', $institution->authentication_url);
        $response->assertViewHas('login_message', $institution->login_message);
    }

    /** @test */
    public function should_redirect_to_institution_authentication_if_login_message_is_null()
    {
        $institution = factory(Institution::class)->create(['login_message' => null]);
        $response = $this->get('login/' . $institution->getRouteKey());

        $response->assertRedirect($institution->authentication_url);
    }

    /** @test */
    public function should_redirect_to_institution_authentication_if_login_message_is_empty()
    {
        $institution = factory(Institution::class)->create(['login_message' => '']);
        $response = $this->get('login/' . $institution->getRouteKey());

        $response->assertRedirect($institution->authentication_url);
    }

    /** @test */
    public function should_return_error_view()
    {
        $errorMessage = 'Error message';
        $response = $this->call('GET', 'login', [ 'error' => $errorMessage ]);

        $response->assertStatus(401);
        $response->assertViewIs('login.error');
        $response->assertViewHas('error', $errorMessage);
    }

    /** @test */
    public function should_remember_login_type()
    {
        $response = $this->call('GET', 'login', [ 'type' => 'app' ]);

        $response->assertSessionHas(LoginController::SESSION_LOGIN_TYPE, 'app');
    }

    /** @test */
    public function should_remember_only_most_recent_login_type()
    {
        $response = $this->withLoginType('app')->call('GET', 'login', [ 'type' => 'web' ]);

        $response->assertSessionHas(LoginController::SESSION_LOGIN_TYPE, 'web');
    }

    /** @test */
    public function should_default_to_web_login_type_when_not_specified()
    {
        $response = $this->call('GET', 'login');

        $response->assertSessionHas(LoginController::SESSION_LOGIN_TYPE, 'web');
    }

    /** @test */
    public function should_return_token_view_with_web_type()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);

        $response = $this->withLoginType('web')->call('GET', 'login', [ 'token' => $jwtToken ]);

        $response->assertStatus(200);
        $response->assertViewIs('login.token');
        $response->assertViewHas('user', $user->fresh());
        $response->assertViewHas('token', $jwtToken);
        $response->assertViewHas('displayTermsOfUse', true);
    }

    /** @test */
    public function should_redirect_to_app_with_app_login_type()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);

        $response = $this->withLoginType('app')->call('GET', 'login', [ 'token' => $jwtToken ]);

        $response->assertRedirect('caronae://login?id=' . $user->id . '&id_ufrj=' . $user->id_ufrj . '&token=' . $user->token);
    }

    /** @test */
    public function should_redirect_to_app_with_jwttoken_with_app_login_type()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);

        $response = $this->withLoginType('app_jwt')->call('GET', 'login', [ 'token' => $jwtToken ]);

        $response->assertRedirect();
        $redirectURL = $response->headers->get('Location');
        $this->assertStringStartsWith('caronae://login?id=' . $user->id . '&token=', $redirectURL);
    }

    /** @test */
    public function should_save_cookie_when_accepting_terms_of_use()
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
    public function should_not_display_terms_again_if_has_accepted_terms()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);

        $cookies = [ 'acceptedTermsOfUse' => Crypt::encrypt(true) ];
        $response = $this->call('GET', 'login', [ 'token' => $jwtToken ], $cookies);

        $response->assertViewHas('displayTermsOfUse', false);
    }

    /** @test */
    public function should_refresh_token()
    {
        $user = factory(User::class)->create();
        $jwtToken = JWTAuth::fromUser($user);
        $previousToken = $user->token;

        $response = $this->post('refreshToken', [ 'token' => $jwtToken ]);

        $response->assertRedirect(route('chave', [ 'token' => $jwtToken ]));
        $this->assertNotEquals($user->fresh()->token, $previousToken);
    }

    private function withLoginType($type)
    {
        return $this->withSession([LoginController::SESSION_LOGIN_TYPE => $type]);
    }
}
