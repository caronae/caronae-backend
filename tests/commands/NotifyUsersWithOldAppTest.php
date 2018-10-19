<?php

namespace Caronae\Console\Commands;


use Carbon\Carbon;
use Caronae\Models\User;
use Caronae\Notifications\UpdateAppNotification;
use Caronae\Services\UserAppService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class NotifyUsersWithOldAppTest extends TestCase
{
    use DatabaseTransactions;

    private $mock;
    private $command;

    protected function setUp()
    {
        parent::setUp();
        Notification::fake();
        $this->mock = Mockery::mock(UserAppService::class);
        $this->command = new NotifyUsersWithOldApp($this->mock);
    }

    /** @test */
    public function should_send_notification_to_users_with_old_app_version()
    {
        $user = factory(User::class)->create();
        $this->mock->shouldReceive('getActiveUsersWithOldAppVersions')->andReturn(collect([$user]));

        $this->command->handle();

        Notification::assertSentTo($user, UpdateAppNotification::class);
    }

    /** @test */
    public function should_not_send_notification_to_users_that_were_notified_recently()
    {
        $userThatWasAlreadyNotified = factory(User::class)->create();
        $userThatWasAlreadyNotified->notifications()->save(new DatabaseNotification([
            'id' => 'E5E33479-92C1-41E9-8505-A18CF854D8D1',
            'type' => UpdateAppNotification::class,
            'notifiable_type' => 'Caronae\Models\User',
            'notifiable_id' => $userThatWasAlreadyNotified->id,
            'data' => '{"userID":3,"app_platform":"iOS","app_version":"1.4.2"}',
            'created_at' => new Carbon('yesterday'),
        ]));

        $userThatWasNotNotified = factory(User::class)->create();
        $userThatWasNotNotified->notifications()->save(new DatabaseNotification([
            'id' => 'E5E33479-92C1-41E9-8505-A18CF854D8D2',
            'type' => 'OtherNotification',
            'notifiable_type' => 'Caronae\Models\User',
            'notifiable_id' => $userThatWasNotNotified->id,
            'data' => '',
            'created_at' => new Carbon('yesterday'),
        ]));

        $this->mock->shouldReceive('getActiveUsersWithOldAppVersions')->andReturn(collect([$userThatWasAlreadyNotified, $userThatWasNotNotified]));

        $this->command->handle();

        Notification::assertSentTo($userThatWasNotNotified, UpdateAppNotification::class);
        Notification::assertNotSentTo($userThatWasAlreadyNotified, UpdateAppNotification::class);
    }
}
