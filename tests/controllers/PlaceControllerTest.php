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

    private $neighborhood;
    private $institution;
    private $campus;

    public function setUp()
    {
        parent::setUp();

        $this->neighborhood = factory(Neighborhood::class)->create();
        $this->institution = factory(Institution::class)->create();
        $this->campus = factory(Campus::class)->create(['institution_id' => $this->institution->id]);
    }

    public function testReturnsZonesAndCampi()
    {
        $hub = factory(Hub::class)->create(['campus_id' => $this->campus->id]);

        $response = $this->json('GET', 'places');

        $response->assertStatus(200);
        $response->assertJson([
            'zones' => [
                [
                    'name' => $this->neighborhood->zone,
                    'neighborhoods' => [ $this->neighborhood->name ]
                ]
            ],
            'campi' => [
                [
                    'name' => $this->campus->name,
                    'color' => $this->campus->color,
                    'centers' => [ $hub->center ],
                    'hubs' => [ $hub->name ]
                ]
            ]
        ]);
    }

    public function testDoesNotIncludeCampiWithoutHubs()
    {
        $response = $this->json('GET', 'places');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'campi' => []
        ]);
    }
}