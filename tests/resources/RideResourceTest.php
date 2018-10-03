<?php

namespace Caronae\Http\Resources;

use Caronae\Models\Ride;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\MissingValue;
use Tests\TestCase;

class RideResourceTest extends TestCase
{
    use DatabaseTransactions;
    private $driver;
    private $ride;
    private $request;

    public function setUp()
    {
        parent::setUp();

        $this->driver = factory(User::class)->create()->fresh();
        $this->ride = factory(Ride::class)->create();
        $this->ride->users()->attach($this->driver, ['status' => 'driver']);
        $this->request = new Request();
    }

    /**
     * @test
     */
    public function should_render_as_json_including_driver()
    {
        $userResource = new UserResource($this->driver);
        $rideResource = new RideResource($this->ride);
        $expectedJson = [
            'id' => $this->ride->id,
            'myzone' => $this->ride->myzone,
            'neighborhood' => $this->ride->neighborhood,
            'going' => $this->ride->going,
            'place' => $this->ride->place,
            'route' => $this->ride->route,
            'routine_id' => $this->ride->routine_id,
            'hub' => $this->ride->hub,
            'slots' => $this->ride->slots,
            'mytime' => $this->ride->date->format('H:i:s'),
            'mydate' => $this->ride->date->format('Y-m-d'),
            'description' => $this->ride->description,
            'week_days' => $this->ride->week_days,
            'repeats_until' => $this->ride->repeats_until,
        ];

        $response = $rideResource->toArray($this->request);

        $this->assertArraySubset($expectedJson, $response);
        $this->assertTrue($response['driver']->is($userResource));
        $this->assertDoesNotShowAttribute('availableSlots', $response);
        $this->assertDoesNotShowAttribute('riders', $response);
    }

    /**
     * @test
     */
    public function should_include_riders_when_loaded()
    {
        $rider = factory(User::class)->create()->fresh();
        $this->ride->users()->attach($rider, ['status' => 'accepted']);
        $this->ride->load('riders');
        $rideResource = new RideResource($this->ride);

        $response = $rideResource->toArray($this->request);

        $ridersResponse = $response['riders']->resource;
        $this->assertEquals(1, count($ridersResponse));
        $this->assertTrue($ridersResponse[0]->is(new UserResource($rider)));
    }

    /**
     * @test
     */
    public function should_include_available_slots()
    {
        $rideResource = new RideResource($this->ride);

        $response = $rideResource->withAvailableSlots()->toArray($this->request);

        $this->assertEquals($this->ride->availableSlots(), $response['availableSlots']);
    }

    private function assertDoesNotShowAttribute($field, $response)
    {
        $fieldValue = $response[$field];
        if ($fieldValue instanceof ResourceCollection) {
            $fieldValue = $fieldValue->resource;
        }
        $this->assertInstanceOf(MissingValue::class, $fieldValue);
    }
}
