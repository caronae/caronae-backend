<?php

namespace Tests;

use Caronae\Models\Hub;
use Caronae\Models\Neighborhood;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlaceControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testReturnsZonesAndCenters()
    {
        $neighborhood = factory(Neighborhood::class)->create();
        $hub = factory(Hub::class)->create();

        $response = $this->json('GET', 'places');

        $response->assertStatus(200);
        $response->assertJson([
            'zones' => [
                [
                    'name' => $neighborhood->zone,
                    'neighborhoods' => [ $neighborhood->name ]
                ]
            ],
            'centers' => [
                [
                    'name' => $hub->center,
                    'campus' => $hub->campus,
                    'hubs' => [ $hub->name ]
                ]
            ]
        ]);
    }
}