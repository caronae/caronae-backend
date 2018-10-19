<?php

use Caronae\Models\Institution;
use Caronae\Models\User;
use Faker\Generator as Faker;

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(User::class, function (Faker $faker) {
    $institutions = Institution::all();
    if ($institutions->count() > 0) {
        $institution = $institutions->random();
    } else {
        $institution = factory(Institution::class)->create();
    }

    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'profile' => $faker->randomElement(['Graduação', 'Mestrado', 'Servidor']),
        'course' => $faker->randomElement(['Engenharia Civil', 'Ciência da Computação', 'Engenharia de Sistemas e Computação', 'Nanotecnologia', 'Letras', 'Medicina', 'Enfermagem']),
        'phone_number' => $faker->regexify('[0-9]{11}'),
        'location' => $faker->city,
        'car_owner' => false,
        'car_model' => NULL,
        'car_color' => NULL,
        'car_plate' => NULL,
        'token' => strtoupper(str_random(6)),
        'id_ufrj' => $faker->unique()->cpf(false),
        'profile_pic_url' => $faker->imageUrl(500, 500, 'people'),
        'face_id' => NULL,
        'institution_id' => $institution->id,
    ];
});

$factory->defineAs(User::class, 'driver', function (Faker $faker) use ($factory) {
    $user = $factory->raw(User::class);
    return array_merge($user, [
        'car_owner' => true,
        'car_model' => $faker->company,
        'car_color' => $faker->colorName,
        'car_plate' => $faker->regexify('[A-Z]{3}-[0-9]{4}')
    ]);
});
