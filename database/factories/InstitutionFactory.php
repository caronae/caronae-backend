<?php

use Caronae\Models\Institution;
use Faker\Generator as Faker;

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Institution::class, function (Faker $faker) {
    $name = $faker->unique()->company;
    return [
        'name' => $name,
        'slug' => $faker->slug,
        'password' => $faker->password,
        'authentication_url' => $faker->url,
        'going_label' => 'Chegando na ' . $name,
        'leaving_label' => 'Saindo da ' . $name,
        'login_message' => $faker->text(50),
    ];
});
