<?php

namespace Caronae\Http\Requests;

use Carbon\Carbon;
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
        Hub::create(['name' => 'CT1', 'center' => 'CT', 'campus_id' => $campus1->id]);
        Hub::create(['name' => 'Outro hub do CT', 'center' => 'CT', 'campus_id' => $campus1->id]);
        Hub::create(['name' => 'PV', 'center' => 'PV', 'campus_id' => $campus2->id]);

        $this->request = new RideListRequest(['campus' => 'Cidade Universitária']);

        $this->assertFilterEquals('hubs', ['CT', 'CT1', 'Outro hub do CT']);
    }

    /** @test */
    public function should_have_a_center_filter_with_a_single_center()
    {
        $institution = factory(Institution::class)->create();
        $campus1 = Campus::create(['name' => 'Cidade Universitária', 'institution_id' => $institution->id]);
        $campus2 = Campus::create(['name' => 'Praia Vermelha', 'institution_id' => $institution->id]);
        Hub::create(['name' => 'CT: Bloco A', 'center' => 'CT', 'campus_id' => $campus1->id]);
        Hub::create(['name' => 'Outro hub do CT', 'center' => 'CT', 'campus_id' => $campus2->id]);
        Hub::create(['name' => 'CCMN: Frente', 'center' => 'CCMN', 'campus_id' => $campus2->id]);

        $this->request = new RideListRequest(['center' => 'CT']);

        $this->assertFilterEquals('hubs', ['CT', 'CT: Bloco A', 'Outro hub do CT']);
    }

    /** @test */
    public function should_have_a_center_filter_with_multiple_centers()
    {
        $institution = factory(Institution::class)->create();
        $campus1 = Campus::create(['name' => 'Cidade Universitária', 'institution_id' => $institution->id]);
        $campus2 = Campus::create(['name' => 'Praia Vermelha', 'institution_id' => $institution->id]);
        Hub::create(['name' => 'CT: Bloco A', 'center' => 'CT', 'campus_id' => $campus1->id]);
        Hub::create(['name' => 'Hub qualquer', 'center' => 'CCS', 'campus_id' => $campus2->id]);
        Hub::create(['name' => 'CCMN: Frente', 'center' => 'CCMN', 'campus_id' => $campus2->id]);

        $this->request = new RideListRequest(['centers' => 'CT, CCS']);

        $this->assertFilterEquals('hubs', ['CT', 'CCS', 'CT: Bloco A', 'Hub qualquer']);
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

    /** @test */
    public function should_return_a_null_date_range_when_not_specified()
    {
        $this->request = new RideListRequest();

        $this->assertNull($this->request->dateRange());
    }

    /** @test */
    public function should_return_the_days_range_when_date_specified()
    {
        $this->request = new RideListRequest(['date' => '2018-10-16']);

        list($dateMin, $dateMax) = $this->request->dateRange();
        $this->assertEquals(Carbon::createMidnightDate(2018, 10, 16), $dateMin);
        $this->assertEquals(Carbon::create(2018, 10, 16, 23, 59, 59), $dateMax);
    }

    /** @test */
    public function should_return_the_rest_of_the_days_range_when_date_and_time_specified()
    {
        $this->request = new RideListRequest(['date' => '2018-10-16', 'time' => '10:45:10']);

        list($dateMin, $dateMax) = $this->request->dateRange();
        $this->assertEquals(Carbon::create(2018, 10, 16, 10, 45, 00), $dateMin);
        $this->assertEquals(Carbon::create(2018, 10, 16, 23, 59, 59), $dateMax);
    }

    private function assertFilterEquals($filter, $expected)
    {
        $this->assertEquals($expected, $this->request->filters()[$filter]);
    }
}
