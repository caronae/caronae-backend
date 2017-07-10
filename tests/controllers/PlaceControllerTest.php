<?php

namespace Tests;

use Caronae\Models\Hub;
use Caronae\Models\Neighborhood;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlaceControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testReturnsZonesAndCampi()
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
            'campi' => [
                [
                    'name' => $hub->campus,
                    'centers' => [ $hub-> center ],
                    'hubs' => [ $hub->name ]
                ]
            ]
        ]);
    }
}