<?php

namespace Tests;

use Carbon\Carbon;
use Caronae\Models\Ride;
use Caronae\Services\ValidateDuplicateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Caronae\Models\User;

class ValidateDuplicateServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function testValidateDuplicatedRidesShouldReturnInvalid()
    {
        $user = factory(User::class)->create()->fresh();
        $dateTime = Carbon::now()->addDays(rand(1,10));

        $ride = factory(Ride::class)->create()->fresh();
        $ride->users()->attach($user->id, ['status' => 'driver']);
        $ride->date = $dateTime;
        $ride->going = true;
        $ride->save();

        $service = new ValidateDuplicateService($ride->users()->first(), $dateTime, true);
        $resultValidadion = $service->validate();
        $this->assertFalse($resultValidadion['valid']);
        $this->assertEquals($resultValidadion['message'], 'The user has already offered a ride on the specified date.');
    }

    public function testValidateNearRidesShouldReturnPossibleDuplicate()
    {
        $user = factory(User::class)->create()->fresh();
        $dateTime = Carbon::now()->addDays(10);

        $ride = factory(Ride::class)->create()->fresh();
        $ride->users()->attach($user->id, ['status' => 'driver']);
        $ride->date = Carbon::now()->addDays(10)->addMinutes(45);
        $ride->going = true;
        $ride->save();

        $service = new ValidateDuplicateService($ride->users()->first(), $dateTime, true);
        $resultValidadion = $service->validate();
        $this->assertFalse($resultValidadion['valid']);
        $this->assertEquals($resultValidadion['message'], 'The user has already offered a ride too close to the specified date.');
    }

    public function testValidateDistinctRidesShouldReturnValid()
    {
        $user = factory(User::class)->create()->fresh();
        $dateTime = Carbon::now()->addDays(10);

        $ride = factory(Ride::class)->create()->fresh();
        $ride->users()->attach($user->id, ['status' => 'driver']);
        $ride->date = Carbon::now()->addDays(11);
        $ride->going = true;
        $ride->save();

        $service = new ValidateDuplicateService($ride->users()->first(), $dateTime, true);
        $resultValidadion = $service->validate();
        $this->assertTrue($resultValidadion['valid']);
        $this->assertEquals($resultValidadion['message'], 'No conflicting rides were found close to the specified date.');
    }
}
