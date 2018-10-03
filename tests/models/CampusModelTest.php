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

    /** @test */
    public function should_find_by_name()
    {
        $campus = factory(Campus::class)->create(['name' => 'Cidade Universitária', 'institution_id' => $this->institution->id])->fresh();
        $campus2 = factory(Campus::class)->create(['name' => 'Praia Vermelha', 'institution_id' => $this->institution->id])->fresh();
        $this->assertEquals($campus, Campus::findByName('Cidade Universitária'));
    }

    /** @test */
    public function should_return_color()
    {
        $campus = factory(Campus::class)->create(['color' => '#ff00ff', 'institution_id' => $this->institution->id]);
        $this->assertEquals('#ff00ff', $campus->color);
    }

    /** @test */
    public function should_default_color_when_empty()
    {
        $campus = factory(Campus::class)->create(['color' => null, 'institution_id' => $this->institution->id]);
        $this->assertEquals(Campus::DEFAULT_COLOR, $campus->color);

        $campus = factory(Campus::class)->create(['color' => '', 'institution_id' => $this->institution->id]);
        $this->assertEquals(Campus::DEFAULT_COLOR, $campus->color);
    }
}
