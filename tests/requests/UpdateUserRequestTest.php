<?php

namespace Caronae\Http\Requests;

use Tests\TestCase;
use Validator;

class UpdateUserRequestTest extends TestCase
{
    private $rules;

    public function setUp()
    {
        parent::setUp();
        $request = new UpdateUserRequest();
        $this->rules = $request->rules();
    }

    /** @test */
    public function should_accept_old_car_plate_with_dash()
    {
        $attributes = ['car_plate' => 'ABC-1234'];

        $validator = Validator::make($attributes, $this->rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function should_accept_old_car_plate_without_dash()
    {
        $attributes = ['car_plate' => 'ABC-1234'];

        $validator = Validator::make($attributes, $this->rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function should_accept_new_car_plate()
    {
        $attributes = ['car_plate' => 'RIO2A18'];

        $validator = Validator::make($attributes, $this->rules);

        $this->assertTrue($validator->passes());
    }
}
