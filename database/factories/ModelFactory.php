<?php

use Caronae\Models\Campus;
use Caronae\Models\Hub;
use Caronae\Models\Institution;
use Caronae\Models\Message;
use Caronae\Models\Neighborhood;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Models\Zone;

$factory->define(Zone::class, function (Faker\Generator $faker) {
    $zones = [
        ['name' => 'Zona Norte'],
        ['name' => 'Zona Sul'],
        ['name' => 'Zona Oeste'],
        ['name' => 'Centro'],
        ['name' => 'Baixada'],
    ];

    return $faker->randomElement($zones);
});

$factory->define(Neighborhood::class, function (Faker\Generator $faker) {
    $neighborhoods = [
        ['name' => 'São Cristóvão', 'distance' => 6.0],
        ['name' => 'Benfica', 'distance' => 6.8],
        ['name' => 'Cidade Nova', 'distance' => 9.8],
        ['name' => 'Saúde', 'distance' => 10.3],
        ['name' => 'Vasco da Gama', 'distance' => 6.7],
        ['name' => 'Botafogo', 'distance' => 16.4],
        ['name' => 'Catete', 'distance' => 14.4],
        ['name' => 'Ipanema', 'distance' => 18.1],
        ['name' => 'Jardim Botânico', 'distance' => 16.2],
        ['name' => 'São Conrado', 'distance' => 23.3],
        ['name' => 'Urca', 'distance' => 16.7],
        ['name' => 'Grumari', 'distance' => 44.9],
        ['name' => 'Méier', 'distance' => 11.8],
        ['name' => 'Piedade', 'distance' => 12.8],
        ['name' => 'Jardim Guanabara', 'distance' => 8.4],
    ];

    $zone = factory(Zone::class)->create();
    $randomNeighborhood = $faker->randomElement($neighborhoods);
    return array_merge($randomNeighborhood, ['zone_id' => $zone->id]);
});

$factory->define(User::class, function (Faker\Generator $faker) {
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
    $neighborhood = factory(Neighborhood::class)->make();

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

$factory->defineAs(Ride::class, 'next', function (Faker\Generator $faker) use ($factory) {
    $ride = $factory->raw(Ride::class);
    $date = $faker->dateTimeBetween('+1 hour', 'tomorrow 23:59:59');
    return array_merge($ride, [
        'date' => $date,
        'done' => false
    ]);
});

$factory->define(Message::class, function (Faker\Generator $faker) {
    return [
        'body' => $faker->text(50),
        'created_at' => $faker->dateTime()
    ];
});

$factory->define(Institution::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->company,
        'password' => $faker->password,
        'authentication_url' => $faker->url
    ];
});

$factory->define(Campus::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->randomElement(['Cidade Universitária', 'Praia Vermelha', 'FND']),
        'color' => $faker->hexColor,
    ];
});

$factory->define(Hub::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->randomElement(['CCS: Frente', 'CCS: HUCFF', 'CCMN: Frente', 'CCMN: Fundos', 'CT: Bloco A', 'CT: Bloco D', 'Letras', 'Reitoria', 'EEFD']),
        'center' => $faker->randomElement(['CCS', 'CCMN', 'CT', 'Letras', 'Reitoria', 'EEFD']),
    ];
});
