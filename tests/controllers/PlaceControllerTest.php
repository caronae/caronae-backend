<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlaceControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function testReturnsNeighborhoodsAndHubs()
    {
        $response = $this->json('GET', 'places');

        $response->assertStatus(200);
        $response->assertJsonStructure(['neighborhoods', 'hubs']);
    }
}