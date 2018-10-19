<?php

namespace Caronae\Console\Commands;


use Caronae\Models\User;
use Caronae\Notifications\UpdateAppNotification;
use Caronae\Services\UserAppService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class NotifyUsersWithOldAppTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function should_send_notification_to_users_with_old_app_version()
    {
        Mail::fake();

        $user = factory(User::class)->create();
        $appServiceMock = Mockery::mock(UserAppService::class);
        $appServiceMock->shouldReceive('getActiveUsersWithOldAppVersions')->andReturn(collect([$user]));

        $this->expectsNotification($user, UpdateAppNotification::class);

        $command = new NotifyUsersWithOldApp($appServiceMock);
        $command->handle();
    }
}
