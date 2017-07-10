<?php

namespace Tests;

use Caronae\Models\Hub;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HubModelTest extends TestCase
{
    use DatabaseTransactions;

    public function testFindsHubsWithCampus()
    {
        $hub = factory(Hub::class)->create(['campus' => 'Cidade Universitária'])->fresh();
        $hub2 = factory(Hub::class)->create(['campus' => 'Praia Vermelha'])->fresh();

        $results = Hub::withCampus('Cidade Universitária')->get();
        $this->assertTrue($results->contains($hub));
        $this->assertFalse($results->contains($hub2));
    }

    public function testFindsByName()
    {
        $hub = factory(Hub::class)->create(['name' => 'CT 1'])->fresh();
        $this->assertEquals($hub, Hub::findByName('CT 1'));
    }
}
