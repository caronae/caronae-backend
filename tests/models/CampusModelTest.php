<?php

namespace Tests\Models;

use Caronae\Models\Campus;
use Caronae\Models\Institution;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CampusModelTest extends TestCase
{
    private $institution;
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->institution = factory(Institution::class)->create();
    }

    public function testFindsByName()
    {
        $campus = factory(Campus::class)->create(['name' => 'Cidade Universitária', 'institution_id' => $this->institution->id])->fresh();
        $campus2 = factory(Campus::class)->create(['name' => 'Praia Vermelha', 'institution_id' => $this->institution->id])->fresh();
        $this->assertEquals($campus, Campus::findByName('Cidade Universitária'));
    }

    public function testReturnsColor()
    {
        $campus = factory(Campus::class)->create(['color' => '#ff00ff', 'institution_id' => $this->institution->id]);
        $this->assertEquals('#ff00ff', $campus->color);
    }

    public function testReturnsDefaultColorWhenEmpty()
    {
        $campus = factory(Campus::class)->create(['color' => null, 'institution_id' => $this->institution->id]);
        $this->assertEquals(Campus::DEFAULT_COLOR, $campus->color);

        $campus = factory(Campus::class)->create(['color' => '', 'institution_id' => $this->institution->id]);
        $this->assertEquals(Campus::DEFAULT_COLOR, $campus->color);
    }
}
