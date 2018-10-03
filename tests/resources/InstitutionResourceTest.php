<?php

namespace Caronae\Http\Resources;

use Caronae\Models\Institution;
use Illuminate\Http\Request;
use Tests\TestCase;

class InstitutionResourceTest extends TestCase
{
    /**
     * @test
     */
    public function should_render_as_json()
    {
        $institution = factory(Institution::class)->create();
        $resource = new InstitutionResource($institution);
        $expectedJson = [
            'name' => $institution->name,
            'going_label' => $institution->going_label,
            'leaving_label' => $institution->leaving_label,
        ];

        $response = $resource->toArray(new Request());

        $this->assertArraySubset($expectedJson, $response);
    }

}
