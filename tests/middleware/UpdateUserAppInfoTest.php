<?php

namespace Caronae\Http\Middleware;


use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\HeaderBag;
use Tests\TestCase;

class UpdateUserAppInfoTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function should_set_ios_app_platform_and_version_on_user()
    {
        $user = factory(User::class)->create();
        $this->authenticateAs($user);

        $request = new Request();
        $request->headers = new HeaderBag(['User-Agent' => 'Caronae/1.5.1 (iPhone; iOS 12.0.1; Scale/3.00)']);
        $middleware = new UpdateUserAppInfo();

        $middleware->handle($request, function($r){ });

        $this->assertDatabaseHas('users', ['id' => $user->id, 'app_platform' => 'iOS', 'app_version' => '1.5.1']);
    }

    /** @test */
    public function should_set_android_app_platform_and_version_on_user()
    {
        $user = factory(User::class)->create();
        $this->authenticateAs($user);

        $request = new Request();
        $request->headers = new HeaderBag(['User-Agent' => 'Caronae/3.0.3 (Samsung: SM-J700M; Android: 6.0.1)']);
        $middleware = new UpdateUserAppInfo();

        $middleware->handle($request, function($r){ });

        $this->assertDatabaseHas('users', ['id' => $user->id, 'app_platform' => 'Android', 'app_version' => '3.0.3']);
    }

    /** @test */
    public function should_set_app_info_from_dev_app()
    {
        $user = factory(User::class)->create();
        $this->authenticateAs($user);

        $request = new Request();
        $request->headers = new HeaderBag(['User-Agent' => 'Caronae Dev/1.5.1 (iPhone; iOS 12.0.1; Scale/3.00)']);
        $middleware = new UpdateUserAppInfo();

        $middleware->handle($request, function($r){ });

        $this->assertDatabaseHas('users', ['id' => $user->id, 'app_platform' => 'iOS', 'app_version' => '1.5.1']);
    }

    /** @test */
    public function should_not_set_app_info_when_guest()
    {
        $request = new Request();
        $request->headers = new HeaderBag(['User-Agent' => 'Caronae/3.0.3 (Samsung: SM-J700M; Android: 6.0.1)']);
        $middleware = new UpdateUserAppInfo();

        $middleware->handle($request, function($r){ });

        $this->assertDatabaseMissing('users', ['app_platform' => 'Android', 'app_version' => '3.0.3']);
    }

    /** @test */
    public function should_not_set_app_info_when_not_ios_android()
    {
        $user = factory(User::class)->create();
        $this->authenticateAs($user);
        $request = new Request();
        $request->headers = new HeaderBag(['User-Agent' => 'PostmanRuntime/7.3.0']);
        $middleware = new UpdateUserAppInfo();

        $middleware->handle($request, function($r){ });

        $this->assertDatabaseMissing('users', ['app_platform' => 'PostmanRuntime', 'app_version' => '7.3.0']);
    }
}
