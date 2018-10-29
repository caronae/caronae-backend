<?php

use Caronae\Models\Neighborhood;
use Caronae\Models\Zone;
use Faker\Generator as Faker;

/*
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Neighborhood::class, function (Faker $faker) {
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
