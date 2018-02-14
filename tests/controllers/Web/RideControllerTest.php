<?php

namespace Caronae\Http\Controllers\Web;

use Carbon\Carbon;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Tests\TestCase;

class RideControllerTest extends TestCase
{
    /** @test */
    public function shouldShowRideView()
    {
        $user = factory(User::class)->create();
        $ride = factory(Ride::class, 'next')->create([
            'date' => Carbon::parse('2017-07-15 12:00:00'),
            'neighborhood' => 'Ipanema',
            'hub' => 'CT',
            'going' => 'true',
        ]);
        $ride->users()->attach($user, ['status' => 'driver']);

        $response = $this->json('GET', 'carona/' . $ride->id);

        $response->assertStatus(200);
        $response->assertViewIs('rides.showWeb');
        $response->assertViewHas('title', 'Ipanema → CT | 15/07 | 12:00');
        $response->assertViewHas('driver', $user->name);
        $response->assertViewHas('deepLinkUrl', 'caronae://carona/' . $ride->id);
    }

    /** @test */
    public function shouldShowRideNotFoundView()
    {
        $response = $this->json('GET', 'carona/666');

        $response->assertStatus(404);
        $response->assertViewIs('rides.notFound');
    }
}
