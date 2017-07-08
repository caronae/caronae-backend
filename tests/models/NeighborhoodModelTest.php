<?php

namespace Tests;

use Caronae\Models\Neighborhood;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NeighborhoodModelTest extends TestCase
{
    use DatabaseTransactions;

    public function testScopeShouldReturnNeighborhoodsWithZone()
    {
        $neighborhood = factory(Neighborhood::class)->create(['zone' => 'Zona Sul'])->fresh();
        $neighborhood2 = factory(Neighborhood::class)->create(['zone' => 'Zona Norte'])->fresh();

        $results = Neighborhood::withZone('Zona Sul')->get();
        $this->assertTrue($results->contains($neighborhood));
        $this->assertFalse($results->contains($neighborhood2));
    }
}
