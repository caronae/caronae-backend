<?php

namespace Caronae\Http\Requests;

use Caronae\Models\Campus;
use Caronae\Models\Hub;
use Caronae\Models\Institution;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RideListRequestTest extends TestCase
{
    use DatabaseTransactions;

    private $request;

    /** @test */
    public function should_have_a_going_filter()
    {
        $this->request = new RideListRequest(['going' => true]);

        $this->assertFilterEquals('going', true);
    }

    /** @test */
    public function should_have_a_neighborhoods_filter()
    {
        $this->request = new RideListRequest(['neighborhoods' => 'Copacabana, Ipanema, Leblon']);

        $this->assertFilterEquals('neighborhoods', ['Copacabana', 'Ipanema', 'Leblon']);
    }

    /** @test */
    public function should_have_a_place_filter()
    {
        $this->request = new RideListRequest(['place' => 'Alvorada']);

        $this->assertFilterEquals('myplace', 'Alvorada');
    }

    /** @test */
    public function should_have_a_zone_filter()
    {
        $this->request = new RideListRequest(['zone' => 'Zona Sul']);

        $this->assertFilterEquals('myzone', 'Zona Sul');
    }

    /** @test */
    public function should_have_a_campus_filter()
    {
        $institution = factory(Institution::class)->create();
        $campus1 = Campus::create(['name' => 'Cidade Universitária', 'institution_id' => $institution->id]);
        $campus2 = Campus::create(['name' => 'Praia Vermelha', 'institution_id' => $institution->id]);
        $hub = Hub::create(['name' => 'CT1', 'center' => 'CT', 'campus_id' => $campus1->id]);
        $hub2 = Hub::create(['name' => 'PV', 'center' => 'PV', 'campus_id' => $campus2->id]);

        $this->request = new RideListRequest(['campus' => 'Cidade Universitária']);

        $this->assertFilterEquals('hubs', ['CT']);
    }

    /** @test */
    public function should_have_a_hub_filter_with_a_single_hub()
    {
        $this->request = new RideListRequest(['hub' => 'CT: Bloco A']);

        $this->assertFilterEquals('hubs', ['CT: Bloco A']);
    }

    /** @test */
    public function should_have_a_hub_filter_with_multiple_hubs()
    {
        $this->request = new RideListRequest(['hubs' => 'CT: Bloco A, CCMN: Frente, CCS']);

        $this->assertFilterEquals('hubs', ['CT: Bloco A', 'CCMN: Frente', 'CCS']);
    }


    private function assertFilterEquals($filter, $expected)
    {
        $this->assertEquals($expected, $this->request->filters()[$filter]);
    }
}
