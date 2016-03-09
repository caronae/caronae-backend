<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'profile' => $faker->titleMale,
        'course' => $faker->company,
        'location' => $faker->city,
    ];
});

$factory->define(App\Ride::class, function (Faker\Generator $faker) {
    return [
        'myzone' => $faker->city,
        'neighborhood' => $faker->city,
        'going' => $faker->boolean(),
        'slots' => $faker->numberBetween(0, 4),
        'mytime' => $faker->time(),
        'mydate' => $faker->date(),
        'done' => $faker->boolean()
    ];
});


