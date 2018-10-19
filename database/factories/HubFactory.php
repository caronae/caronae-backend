<?php

use Caronae\Models\Hub;
use Faker\Generator as Faker;

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Hub::class, function (Faker $faker) {
    return [
        'name' => $faker->randomElement(['CCS: Frente', 'CCS: HUCFF', 'CCMN: Frente', 'CCMN: Fundos', 'CT: Bloco A', 'CT: Bloco D', 'Letras', 'Reitoria', 'EEFD']),
        'center' => $faker->randomElement(['CCS', 'CCMN', 'CT', 'Letras', 'Reitoria', 'EEFD']),
    ];
});
