<?php

use Caronae\Models\Institution;
use Caronae\Models\User;
use Caronae\Models\Ride;
use Illuminate\Database\Seeder;

class UserRideTableSeeder extends Seeder
{
	protected $drivers;
	protected $rides;
	protected $riders;
	protected $faker;
    protected $institutions;

    public function __construct(Faker\Generator $faker)
	{
		$this->faker = $faker;
		$this->institutions = Institution::all();
	}

    public function run()
    {
        $this->createDrivers();
        echo "Created " . count($this->drivers) . " drivers offering " . count($this->rides) . " rides.\n";

        $this->createRiders();
	    echo "Created " . count($this->riders) . " riders.\n";

	    echo "Users table size: " . User::count() . "\n";
	    echo "Rides table size: " . Ride::count() . "\n";
    }

    protected function createDrivers()
    {
    	$rides = [];

    	$this->drivers = factory(User::class, 'driver', 50)->create()->each(function($user) use (&$rides) {
        	// Create a random number of rides offered by the user
        	$rides_offered_count = $this->faker->numberBetween(0, 10);
        	for ($i=0; $i<$rides_offered_count; $i++) {
	        	$ride = factory(Ride::class)->make();
		        $user->rides()->save($ride, ['status' => 'driver']);
	        	$rides[] = $ride;
	        }

	        // Create a random number of rides in the future
        	$rides_offered_count = $this->faker->numberBetween(0, 10);
        	for ($i=0; $i<$rides_offered_count; $i++) {
	        	$ride = factory(Ride::class, 'next')->make();
		        $user->rides()->save($ride, ['status' => 'driver']);
	        	$rides[] = $ride;
	        }
	    });

		$this->rides = $rides;	
    }

    protected function createRiders()
    {
        $this->riders = factory(User::class, 100)->create()->each(function($user) {
        	$ride = $this->faker->randomElement($this->rides);
        	$status = $this->faker->randomElement(['pending', 'accepted', 'refused']);
	        $user->rides()->attach($ride, ['status' => $status]);
	    });
    }
}