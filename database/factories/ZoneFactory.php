<?php

use Caronae\Models\Zone;
use Faker\Generator as Faker;

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Zone::class, function (Faker $faker) {
    $zones = [
        ['name' => 'Zona Norte'],
        ['name' => 'Zona Sul'],
        ['name' => 'Zona Oeste'],
        ['name' => 'Centro'],
        ['name' => 'Baixada'],
    ];

    return $faker->randomElement($zones);
});
