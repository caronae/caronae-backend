<?php

use Caronae\Models\Neighborhood;
use Caronae\Models\Ride;
use Faker\Generator as Faker;

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Ride::class, function (Faker $faker) {
    $neighborhoods = Neighborhood::all();
    if ($neighborhoods->count() > 0) {
        $neighborhood = $neighborhoods->random();
    } else {
        $neighborhood = factory(Neighborhood::class)->create();
    }

    $going = $faker->boolean();
    if ($going) {
        $hub = $faker->randomElement(['CCS', 'CCMN', 'CT', 'Letras', 'Reitoria', 'EEFD']);
    } else {
        $hub = $faker->randomElement(['CCS: Frente', 'CCS: HUCFF', 'CCMN: Frente', 'CCMN: Fundos', 'CT: Bloco A', 'CT: Bloco D', 'Letras', 'Reitoria', 'EEFD']);
    }

    return [
        'myzone' => $neighborhood->zone->name,
        'neighborhood' => $neighborhood->name,
        'going' => $going,
        'place' => $faker->streetName,
        'route' => $faker->streetName . ', ' . $faker->streetName . ', ' . $faker->streetName,
        'description' => $faker->text(100),
        'hub' => $hub,
        'slots' => $faker->numberBetween(1, 4),
        'date' => $faker->dateTime(),
        'done' => $faker->boolean()
    ];
});

$factory->defineAs(Ride::class, 'next', function (Faker $faker) use ($factory) {
    $ride = $factory->raw(Ride::class);
    $date = $faker->dateTimeBetween('+1 hour', 'tomorrow 23:59:59');
    return array_merge($ride, [
        'date' => $date,
        'done' => false
    ]);
});
