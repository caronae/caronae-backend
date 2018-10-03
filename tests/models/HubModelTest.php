<?php

namespace Tests;

use Caronae\Models\Campus;
use Caronae\Models\Hub;
use Caronae\Models\Institution;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HubModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function should_find_by_name()
    {
        $institution = factory(Institution::class)->create();
        $campus = factory(Campus::class)->create(['institution_id' => $institution->id]);
        $hub1 = factory(Hub::class)->create(['name' => 'CT 1', 'campus_id' => $campus->id])->fresh();
        $hub2 = factory(Hub::class)->create(['name' => 'CT 2', 'campus_id' => $campus->id])->fresh();
        $this->assertEquals($hub1, Hub::findByName('CT 1'));
    }
}
