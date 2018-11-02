<?php

namespace Caronae\Services;

use Carbon\Carbon;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserAppServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var UserAppService
     */
    private $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = new UserAppService();
    }

    /** @test */
    public function should_return_ios_users_with_app_versions_prior_to_1_5_0()
    {
        $userOld = factory(User::class)->create(['app_platform' => 'iOS', 'app_version' => '1.4.0']);
        $userVeryOld = factory(User::class)->create(['app_platform' => 'iOS', 'app_version' => '1.0.1']);

        $users = $this->service->getActiveUsersWithOldAppVersions();

        $this->assertTrue($users->contains($userOld));
        $this->assertTrue($users->contains($userVeryOld));
    }

    /** @test */
    public function should_not_return_ios_users_with_app_versions_equal_or_later_than_1_5_0()
    {
        $userNew = factory(User::class)->create(['app_platform' => 'iOS', 'app_version' => '1.5.0']);
        $userVeryNew = factory(User::class)->create(['app_platform' => 'iOS', 'app_version' => '2.0.0']);

        $users = $this->service->getActiveUsersWithOldAppVersions();

        $this->assertFalse($users->contains($userNew));
        $this->assertFalse($users->contains($userVeryNew));
    }

    /** @test */
    public function should_return_android_users_with_app_versions_prior_to_3_0_3()
    {
        $userOld = factory(User::class)->create(['app_platform' => 'Android', 'app_version' => '3.0.2']);
        $userVeryOld = factory(User::class)->create(['app_platform' => 'Android', 'app_version' => '2.2.6']);
        $userVeryVeryOld = factory(User::class)->create(['app_platform' => 'Android', 'app_version' => '2.1.3']);

        $users = $this->service->getActiveUsersWithOldAppVersions();

        $this->assertTrue($users->contains($userOld));
        $this->assertTrue($users->contains($userVeryOld));
        $this->assertTrue($users->contains($userVeryVeryOld));
    }

    /** @test */
    public function should_not_return_android_users_with_app_versions_equal_or_later_then_3_0_3()
    {
        $userNew = factory(User::class)->create(['app_platform' => 'Android', 'app_version' => '3.0.3']);
        $userVeryNew = factory(User::class)->create(['app_platform' => 'Android', 'app_version' => '4.0.0']);

        $users = $this->service->getActiveUsersWithOldAppVersions();

        $this->assertFalse($users->contains($userNew));
        $this->assertFalse($users->contains($userVeryNew));
    }

    /** @test */
    public function should_return_users_that_were_active_in_the_last_15_days()
    {
        $userTwoWeeksAgo = factory(User::class)->create(['updated_at' => new Carbon('14 days ago'), 'app_platform' => 'Android', 'app_version' => '1.0.0']);
        $userYesterday = factory(User::class)->create(['updated_at' => new Carbon('yesterday'), 'app_platform' => 'Android', 'app_version' => '1.0.0']);

        $users = $this->service->getActiveUsersWithOldAppVersions();

        $this->assertTrue($users->contains($userTwoWeeksAgo));
        $this->assertTrue($users->contains($userYesterday));
    }

    /** @test */
    public function should_not_return_users_that_were_not_active_in_the_last_15_days()
    {
        $userTwoWeeksAgo = factory(User::class)->create(['updated_at' => new Carbon('16 days ago'), 'app_platform' => 'Android', 'app_version' => '1.0.0']);
        $userALongTimeAgo = factory(User::class)->create(['updated_at' => new Carbon('1 year ago'), 'app_platform' => 'Android', 'app_version' => '1.0.0']);

        $users = $this->service->getActiveUsersWithOldAppVersions();

        $this->assertFalse($users->contains($userTwoWeeksAgo));
        $this->assertFalse($users->contains($userALongTimeAgo));
    }
}
