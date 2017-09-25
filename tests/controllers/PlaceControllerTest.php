<?php

namespace Tests;

use Caronae\Models\Campus;
use Caronae\Models\Hub;
use Caronae\Models\Institution;
use Caronae\Models\Neighborhood;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlaceControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testReturnsZonesAndCampi()
    {
        $neighborhood = factory(Neighborhood::class)->create();
        $institution = factory(Institution::class)->create();
        $campus = factory(Campus::class)->create(['institution_id' => $institution->id]);
        $hub = factory(Hub::class)->create(['campus_id' => $campus->id]);

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
                    'name' => $campus->name,
                    'centers' => [ $hub->center ],
                    'hubs' => [ $hub->name ]
                ]
            ]
        ]);
    }
}