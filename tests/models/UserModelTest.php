<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Models\User;

class UserModelTest extends TestCase
{
    use DatabaseTransactions;

    public function testNotificationShouldUseToken_whenHasTokenAndOldAppVersion()
    {
        $user = factory(User::class)->create([
            'gcm_token' => 'token',
            'app_platform' => 'iOS',
            'app_version' => '1.0.3'
        ]);
        $this->assertTrue($user->usesNotificationsWithToken());
        
        $user->app_platform = 'Android';
        $user->app_version = '1.0.6';
        $this->assertTrue($user->usesNotificationsWithToken());
    }

    public function testNotificationShouldUseToken_whenHasTokenAndUnknownAppVersion()
    {
        $user = factory(User::class)->create([
            'gcm_token' => 'token',
            'app_platform' => null,
            'app_version' => null
        ]);
        $this->assertTrue($user->usesNotificationsWithToken());
    }

    public function testNotificationShouldNotUseToken_whenHasTokenAndNewAppVersion()
    {
        $user = factory(User::class)->create([
            'gcm_token' => 'token',
            'app_platform' => 'iOS',
            'app_version' => '1.1.0'
        ]);
        $this->assertFalse($user->usesNotificationsWithToken());
        
        $user->app_platform = 'Android';
        $user->app_version = '1.5';
        $this->assertFalse($user->usesNotificationsWithToken());

        $user->app_version = '2.0';
        $this->assertFalse($user->usesNotificationsWithToken());
    }

    public function testNotificationShouldNotUseToken_whenTokenEmpty()
    {
        $user = factory(User::class)->create([
            'gcm_token' => '',
            'app_platform' => 'iOS',
            'app_version' => '1.1.0'
        ]);
        $this->assertFalse($user->usesNotificationsWithToken());
        
        $user->app_platform = 'Android';
        $user->app_version = '1.0.5';
        $this->assertFalse($user->usesNotificationsWithToken());

        $user->app_platform = 'iOS';
        $user->app_version = '1.0.3';
        $this->assertFalse($user->usesNotificationsWithToken());
    }
}
