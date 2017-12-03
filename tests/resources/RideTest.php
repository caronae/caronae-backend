<?php

namespace Caronae\Http\Resources;

use Caronae\Models\Ride as RideModel;
use Caronae\Models\User as UserModel;
use Illuminate\Http\Request;
use Tests\TestCase;

class RideTest extends TestCase
{
    /**
     * @test
     */
    public function shouldRenderAsJsonIncludingDriver()
    {
        $driver = factory(UserModel::class)->create();
        $ride = factory(RideModel::class)->create();
        $ride->users()->attach($driver, ['status' => 'driver']);
        $userResource = new User($driver);
        $rideResource = new Ride($ride);
        $request = new Request();

        $expectedJson = [
            'id' => $ride->id,
            'myzone' => $ride->myzone,
            'neighborhood' => $ride->neighborhood,
            'going' => $ride->going,
            'place' => $ride->place,
            'route' => $ride->route,
            'routine_id' => $ride->routine_id,
            'hub' => $ride->hub,
            'slots' => $ride->slots,
            'mytime' => $ride->date->format('H:i:s'),
            'mydate' => $ride->date->format('Y-m-d'),
            'description' => $ride->description,
            'week_days' => $ride->week_days,
            'repeats_until' => $ride->repeats_until,
            'driver' => $userResource->toArray($request),
        ];

        $this->assertArraySubset($expectedJson, $rideResource->toArray($request));
    }

    /**
     * @test
     */
    public function shouldIncludeRidersWhenLoaded()
    {
        $driver = factory(UserModel::class)->create();
        $rider = factory(UserModel::class)->create()->fresh();
        $ride = factory(RideModel::class)->create();
        $ride->users()->attach($driver, ['status' => 'driver']);
        $ride->users()->attach($rider, ['status' => 'accepted']);

        $ride->load('riders');
        $rideResource = new Ride($ride);
        $riderResource = new User($rider);
        $request = new Request();

        $rideJson = $rideResource->toArray($request);
        $ridersJson = $rideJson['riders']->toArray($request);

        $this->assertEquals([$riderResource->toArray($request)], $ridersJson);
    }
}
