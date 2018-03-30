<?php

namespace Tests;

use Caronae\Http\Resources\InstitutionResource;
use Caronae\Models\Campus;
use Caronae\Models\Hub;
use Caronae\Models\Institution;
use Caronae\Models\Neighborhood;
use Caronae\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlaceControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $neighborhood;
    private $institution;
    private $campus;
    private $zone;
    private $user;
    private $hub;

    public function setUp()
    {
        parent::setUp();

        $this->neighborhood = factory(Neighborhood::class)->create();
        $this->zone = $this->neighborhood->zone()->first();
        $this->institution = factory(Institution::class)->create();
        $this->campus = factory(Campus::class)->create(['institution_id' => $this->institution->id]);
        $this->hub = factory(Hub::class)->create(['campus_id' => $this->campus->id]);
        $this->user = factory(User::class)->create(['institution_id' => $this->institution->id]);

        $otherInstitution = factory(Institution::class)->create();
        $otherCampus = factory(Campus::class)->create(['institution_id' => $otherInstitution->id]);
        $otherHub = factory(Hub::class)->create(['campus_id' => $otherCampus->id]);
    }

    /** @test */
    public function should_return_zones()
    {
        $response = $this->jsonAs($this->user,'GET', 'api/v1/places');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'zones' => [
                [
                    'name' => $this->zone->name,
                    'color' => $this->zone->color,
                    'neighborhoods' => [ $this->neighborhood->name ]
                ],
            ],
        ]);
    }

    /** @test */
    public function should_return_users_institution()
    {
        $response = $this->jsonAs($this->user,'GET', 'api/v1/places');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'institution' => (new InstitutionResource($this->institution))->resolve(),
        ]);
    }

    /** @test */
    public function should_return_campi_from_users_institution()
    {
        $response = $this->jsonAs($this->user,'GET', 'api/v1/places');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'campi' => [
                [
                    'name' => $this->campus->name,
                    'color' => $this->campus->color,
                    'centers' => [ $this->hub->center ],
                    'hubs' => [ $this->hub->name ]
                ],
            ]
        ]);
    }

    /** @test */
    public function should_not_include_campi_without_hubs()
    {
        $campusWithoutHubs = factory(Campus::class)->create(['institution_id' => $this->institution->id]);

        $response = $this->jsonAs($this->user,'GET', 'api/v1/places');

        $response->assertStatus(200);
        $campi = $response->getOriginalContent()['campi'];
        $this->assertEquals(1, $campi->count());
    }

    /** @test */
    public function should_return_error_when_not_authenticated()
    {
        $response = $this->json('GET', 'api/v1/places');

        $response->assertStatus(401);
    }
}