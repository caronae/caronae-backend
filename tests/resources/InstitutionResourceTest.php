<?php
/**
 * Created by PhpStorm.
 * User: mcecchi
 * Date: 15/03/2018
 * Time: 00:06
 */

namespace Caronae\Http\Resources;


use Caronae\Models\Institution;
use Illuminate\Http\Request;
use Tests\TestCase;

class InstitutionResourceTest extends TestCase
{
    /**
     * @test
     */
    public function shouldRenderAsJson()
    {
        $institution = factory(Institution::class)->create();
        $resource = new InstitutionResource($institution);
        $expectedJson = [
            'name' => $institution->name,
        ];

        $response = $resource->toArray(new Request());

        $this->assertArraySubset($expectedJson, $response);
    }

}
