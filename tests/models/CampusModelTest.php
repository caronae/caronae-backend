<?php

namespace Tests;

use Caronae\Models\Campus;
use Caronae\Models\Institution;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CampusModelTest extends TestCase
{
    use DatabaseTransactions;

    public function testFindsByName()
    {
        $institution = factory(Institution::class)->create();
        $campus = factory(Campus::class)->create(['name' => 'Cidade Universitária', 'institution_id' => $institution->id])->fresh();
        $campus2 = factory(Campus::class)->create(['name' => 'Praia Vermelha', 'institution_id' => $institution->id])->fresh();
        $this->assertEquals($campus, Campus::findByName('Cidade Universitária'));
    }
}
