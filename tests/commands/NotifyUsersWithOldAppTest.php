<?php

namespace Caronae\Console\Commands;


use Caronae\Models\User;
use Caronae\Notifications\UpdateAppNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyUsersWithOldAppTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function should_send_notification_to_()
    {
        Mail::fake();

        $user = factory(User::class)->create(['email' => 'macecchi@gmail.com']);
        $this->expectsNotification($user, UpdateAppNotification::class);

        $command = new NotifyUsersWithOldApp();
        $command->handle();
    }
}
