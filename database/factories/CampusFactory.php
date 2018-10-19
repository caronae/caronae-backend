<?php

use Caronae\Models\Campus;
use Faker\Generator as Faker;

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Campus::class, function (Faker $faker) {
    return [
        'name' => $faker->randomElement(['Cidade Universitária', 'Praia Vermelha', 'FND']),
        'color' => $faker->hexColor,
    ];
});
