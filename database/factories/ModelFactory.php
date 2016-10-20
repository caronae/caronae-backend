<?php
use App\User;
use App\Ride;
use App\RideUser;

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

$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'profile' => $faker->titleMale,
        'course' => $faker->company,
        'location' => $faker->city,
        'car_owner' => false,
        'car_model' => NULL,
        'car_color' => NULL,
        'car_plate' => NULL
    ];
});

$factory->defineAs(User::class, 'driver', function (Faker\Generator $faker) use ($factory) {
    $user = $factory->raw(User::class);
    return array_merge($user, [
        'car_owner' => true,
        'car_model' => $faker->company,
        'car_color' => $faker->colorName,
        'car_plate' => $faker->regexify('[A-Z]{3}-[0-9]{4}')
    ]);
});

$factory->define(Ride::class, function (Faker\Generator $faker) {
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
