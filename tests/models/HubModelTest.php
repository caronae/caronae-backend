<?php

namespace Tests;

use Caronae\Models\Hub;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HubModelTest extends TestCase
{
    use DatabaseTransactions;

    public function testScopeShouldReturnHubsWithCampus()
    {
        $hub = factory(Hub::class)->create(['campus' => 'Cidade Universitária'])->fresh();
        $hub2 = factory(Hub::class)->create(['campus' => 'Praia Vermelha'])->fresh();

        $results = Hub::withCampus('Cidade Universitária')->get();
        $this->assertTrue($results->contains($hub));
        $this->assertFalse($results->contains($hub2));
    }
}
