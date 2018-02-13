<?php

namespace Caronae\Models;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ZoneTest extends TestCase
{
    use DatabaseTransactions;

    public function testFindsByName()
    {
        $zone1 = Zone::create(['name' => 'Zona 123'])->fresh();
        $zone2 = Zone::create(['name' => 'Centro']);
        $this->assertEquals($zone1, Zone::findByName('Zona 123'));
    }

    public function testReturnsColor()
    {
        $zone = Zone::create(['color' => '#ff00ff', 'name' => 'Zona']);
        $this->assertEquals('#ff00ff', $zone->color);
    }

    public function testReturnsDefaultColorWhenEmpty()
    {
        $zone = Zone::create(['color' => null, 'name' => 'Zona']);
        $this->assertEquals(Campus::DEFAULT_COLOR, $zone->color);

        $zone = Zone::create(['color' => '', 'name' => 'Zona']);
        $this->assertEquals(Campus::DEFAULT_COLOR, $zone->color);
    }
}
