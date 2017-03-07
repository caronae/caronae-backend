<?php
use Caronae\Models\Message;
use Caronae\Models\Neighborhood;
use Caronae\Models\User;
use Caronae\Models\Ride;

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

$factory->define(Neighborhood::class, function (Faker\Generator $faker) {
    $neighborhoods = [
        ['zone' => 'Centro', 'name' => 'São Cristóvão', 'distance' => 6.0],
        ['zone' => 'Centro', 'name' => 'Benfica', 'distance' => 6.8],
        ['zone' => 'Centro', 'name' => 'Caju', 'distance' => 6.3],
        ['zone' => 'Centro', 'name' => 'Catumbi', 'distance' => 12.6],
        ['zone' => 'Centro', 'name' => 'Centro (Bairro)', 'distance' => 11.6],
        ['zone' => 'Centro', 'name' => 'Cidade Nova', 'distance' => 9.8],
        ['zone' => 'Centro', 'name' => 'Estácio', 'distance' => 9.6],
        ['zone' => 'Centro', 'name' => 'Gamboa', 'distance' => 8.9],
        ['zone' => 'Centro', 'name' => 'Glória', 'distance' => 13.3],
        ['zone' => 'Centro', 'name' => 'Lapa', 'distance' => 11.6],
        ['zone' => 'Centro', 'name' => 'Mangueira', 'distance' => 9.2],
        ['zone' => 'Centro', 'name' => 'Paquetá', 'distance' => 11.0],
        ['zone' => 'Centro', 'name' => 'Rio Comprido', 'distance' => 10.2],
        ['zone' => 'Centro', 'name' => 'Santa Teresa', 'distance' => 11.8],
        ['zone' => 'Centro', 'name' => 'Santo Cristo', 'distance' => 8.0],
        ['zone' => 'Centro', 'name' => 'Saúde', 'distance' => 10.3],
        ['zone' => 'Centro', 'name' => 'Vasco da Gama', 'distance' => 6.7],
        ['zone' => 'Zona Sul', 'name' => 'Botafogo', 'distance' => 16.4],
        ['zone' => 'Zona Sul', 'name' => 'Catete', 'distance' => 14.4],
        ['zone' => 'Zona Sul', 'name' => 'Copacabana', 'distance' => 18.1],
        ['zone' => 'Zona Sul', 'name' => 'Cosme Velho', 'distance' => 16.0],
        ['zone' => 'Zona Sul', 'name' => 'Flamengo', 'distance' => 14.4],
        ['zone' => 'Zona Sul', 'name' => 'Gávea', 'distance' => 18.2],
        ['zone' => 'Zona Sul', 'name' => 'Humaitá', 'distance' => 14.5],
        ['zone' => 'Zona Sul', 'name' => 'Ipanema', 'distance' => 18.1],
        ['zone' => 'Zona Sul', 'name' => 'Jardim Botânico', 'distance' => 16.2],
        ['zone' => 'Zona Sul', 'name' => 'Lagoa', 'distance' => 15.2],
        ['zone' => 'Zona Sul', 'name' => 'Laranjeiras', 'distance' => 14.4],
        ['zone' => 'Zona Sul', 'name' => 'Leblon', 'distance' => 17.8],
        ['zone' => 'Zona Sul', 'name' => 'Leme', 'distance' => 18.4],
        ['zone' => 'Zona Sul', 'name' => 'Rocinha', 'distance' => 22.2],
        ['zone' => 'Zona Sul', 'name' => 'São Conrado', 'distance' => 23.3],
        ['zone' => 'Zona Sul', 'name' => 'Urca', 'distance' => 16.7],
        ['zone' => 'Zona Sul', 'name' => 'Vidigal', 'distance' => 20.1],
        ['zone' => 'Zona Oeste', 'name' => 'Anil', 'distance' => 21.8],
        ['zone' => 'Zona Oeste', 'name' => 'Barra da Tijuca', 'distance' => 26.7],
        ['zone' => 'Zona Oeste', 'name' => 'Camorim', 'distance' => 31.5],
        ['zone' => 'Zona Oeste', 'name' => 'Cidade de Deus', 'distance' => 21.7],
        ['zone' => 'Zona Oeste', 'name' => 'Curicica', 'distance' => 22.5],
        ['zone' => 'Zona Oeste', 'name' => 'Freguesia de Jacarepaguá', 'distance' => 17.1],
        ['zone' => 'Zona Oeste', 'name' => 'Gardênia Azul', 'distance' => 20.6],
        ['zone' => 'Zona Oeste', 'name' => 'Grumari', 'distance' => 44.9],
        ['zone' => 'Zona Oeste', 'name' => 'Itanhangá', 'distance' => 31.9],
        ['zone' => 'Zona Oeste', 'name' => 'Jacarepaguá', 'distance' => 25.2],
        ['zone' => 'Zona Oeste', 'name' => 'Joá', 'distance' => 26.0],
        ['zone' => 'Zona Oeste', 'name' => 'Praça Seca', 'distance' => 18.0],
        ['zone' => 'Zona Oeste', 'name' => 'Pechincha', 'distance' => 17.3],
        ['zone' => 'Zona Oeste', 'name' => 'Recreio dos Bandeirantes', 'distance' => 35.6],
        ['zone' => 'Zona Oeste', 'name' => 'Tanque', 'distance' => 19.0],
        ['zone' => 'Zona Oeste', 'name' => 'Taquara', 'distance' => 19.7],
        ['zone' => 'Zona Oeste', 'name' => 'Vargem Grande', 'distance' => 39.0],
        ['zone' => 'Zona Oeste', 'name' => 'Vargem Pequena', 'distance' => 34.3],
        ['zone' => 'Zona Oeste', 'name' => 'Vila Valqueire', 'distance' => 18.8],
        ['zone' => 'Zona Oeste', 'name' => 'Bangu', 'distance' => 30.5],
        ['zone' => 'Zona Oeste', 'name' => 'Deodoro', 'distance' => 20.9],
        ['zone' => 'Zona Oeste', 'name' => 'Gericinó', 'distance' => 32.7],
        ['zone' => 'Zona Oeste', 'name' => 'Jardim Sulacap', 'distance' => 21.7],
        ['zone' => 'Zona Oeste', 'name' => 'Magalhães Bastos', 'distance' => 25.5],
        ['zone' => 'Zona Oeste', 'name' => 'Padre Miguel', 'distance' => 27.9],
        ['zone' => 'Zona Oeste', 'name' => 'Realengo', 'distance' => 26.3],
        ['zone' => 'Zona Oeste', 'name' => 'Santíssimo', 'distance' => 37.1],
        ['zone' => 'Zona Oeste', 'name' => 'Senador Camará', 'distance' => 33.6],
        ['zone' => 'Zona Oeste', 'name' => 'Vila Militar', 'distance' => 22.5],
        ['zone' => 'Zona Oeste', 'name' => 'Barra de Guaratiba', 'distance' => 48.9],
        ['zone' => 'Zona Oeste', 'name' => 'Campo Grande', 'distance' => 42.9],
        ['zone' => 'Zona Oeste', 'name' => 'Cosmos', 'distance' => 51.5],
        ['zone' => 'Zona Oeste', 'name' => 'Guaratiba', 'distance' => 49.5],
        ['zone' => 'Zona Oeste', 'name' => 'Inhoaíba', 'distance' => 53.7],
        ['zone' => 'Zona Oeste', 'name' => 'Paciência', 'distance' => 53.5],
        ['zone' => 'Zona Oeste', 'name' => 'Pedra de Guaratiba', 'distance' => 54.0],
        ['zone' => 'Zona Oeste', 'name' => 'Santa Cruz', 'distance' => 56.0],
        ['zone' => 'Zona Oeste', 'name' => 'Senador Vasconcelos', 'distance' => 40.5],
        ['zone' => 'Zona Oeste', 'name' => 'Sepetiba', 'distance' => 61.7],
        ['zone' => 'Zona Norte', 'name' => 'Alto da Boa Vista', 'distance' => 21.9],
        ['zone' => 'Zona Norte', 'name' => 'Andaraí', 'distance' => 11.9],
        ['zone' => 'Zona Norte', 'name' => 'Grajaú', 'distance' => 12.6],
        ['zone' => 'Zona Norte', 'name' => 'Maracanã', 'distance' => 10.1],
        ['zone' => 'Zona Norte', 'name' => 'Praça da Bandeira', 'distance' => 8.3],
        ['zone' => 'Zona Norte', 'name' => 'Tijuca', 'distance' => 12.9],
        ['zone' => 'Zona Norte', 'name' => 'Vila Isabel', 'distance' => 11.1],
        ['zone' => 'Zona Norte', 'name' => 'Abolição', 'distance' => 11.1],
        ['zone' => 'Zona Norte', 'name' => 'Água Santa', 'distance' => 12.6],
        ['zone' => 'Zona Norte', 'name' => 'Cachambi', 'distance' => 8.6],
        ['zone' => 'Zona Norte', 'name' => 'Del Castilho', 'distance' => 6.7],
        ['zone' => 'Zona Norte', 'name' => 'Encantado', 'distance' => 11.1],
        ['zone' => 'Zona Norte', 'name' => 'Engenho de Dentro', 'distance' => 11.6],
        ['zone' => 'Zona Norte', 'name' => 'Engenho Novo', 'distance' => 10.5],
        ['zone' => 'Zona Norte', 'name' => 'Inhaúma', 'distance' => 7.5],
        ['zone' => 'Zona Norte', 'name' => 'Jacaré', 'distance' => 7.9],
        ['zone' => 'Zona Norte', 'name' => 'Jacarezinho', 'distance' => 8.2],
        ['zone' => 'Zona Norte', 'name' => 'Lins de Vasconcelos', 'distance' => 12.4],
        ['zone' => 'Zona Norte', 'name' => 'Maria da Graça', 'distance' => 7.5],
        ['zone' => 'Zona Norte', 'name' => 'Méier', 'distance' => 11.8],
        ['zone' => 'Zona Norte', 'name' => 'Piedade', 'distance' => 12.8],
        ['zone' => 'Zona Norte', 'name' => 'Pilares', 'distance' => 10.7],
        ['zone' => 'Zona Norte', 'name' => 'Riachuelo', 'distance' => 8.2],
        ['zone' => 'Zona Norte', 'name' => 'Rocha', 'distance' => 9.8],
        ['zone' => 'Zona Norte', 'name' => 'Sampaio', 'distance' => 8.6],
        ['zone' => 'Zona Norte', 'name' => 'São Francisco Xavier', 'distance' => 10.2],
        ['zone' => 'Zona Norte', 'name' => 'Todos os Santos', 'distance' => 11.5],
        ['zone' => 'Zona Norte', 'name' => 'Bancários', 'distance' => 11.7],
        ['zone' => 'Zona Norte', 'name' => 'Cacuia', 'distance' => 11.4],
        ['zone' => 'Zona Norte', 'name' => 'Cidade Universitária', 'distance' => 0.0],
        ['zone' => 'Zona Norte', 'name' => 'Cocotá', 'distance' => 11.9],
        ['zone' => 'Zona Norte', 'name' => 'Freguesia (Ilha do Governador)', 'distance' => 13.1],
        ['zone' => 'Zona Norte', 'name' => 'Galeão', 'distance' => 6.7],
        ['zone' => 'Zona Norte', 'name' => 'Jardim Carioca', 'distance' => 10.0],
        ['zone' => 'Zona Norte', 'name' => 'Jardim Guanabara', 'distance' => 8.4],
        ['zone' => 'Zona Norte', 'name' => 'Maré', 'distance' => 3.7],
        ['zone' => 'Zona Norte', 'name' => 'Monero', 'distance' => 10.2],
        ['zone' => 'Zona Norte', 'name' => 'Pitangueiras', 'distance' => 12.0],
        ['zone' => 'Zona Norte', 'name' => 'Portuguesa', 'distance' => 10.0],
        ['zone' => 'Zona Norte', 'name' => 'Praia da Bandeira', 'distance' => 12.2],
        ['zone' => 'Zona Norte', 'name' => 'Ribeira', 'distance' => 13.6],
        ['zone' => 'Zona Norte', 'name' => 'Tauá', 'distance' => 10.7],
        ['zone' => 'Zona Norte', 'name' => 'Zumbi', 'distance' => 12.5],
        ['zone' => 'Zona Norte', 'name' => 'Acari', 'distance' => 15.7],
        ['zone' => 'Zona Norte', 'name' => 'Anchieta', 'distance' => 22.8],
        ['zone' => 'Zona Norte', 'name' => 'Barros Filho', 'distance' => 17.3],
        ['zone' => 'Zona Norte', 'name' => 'Bento Ribeiro', 'distance' => 19.1],
        ['zone' => 'Zona Norte', 'name' => 'Brás de Pina', 'distance' => 14.6],
        ['zone' => 'Zona Norte', 'name' => 'Bonsucesso', 'distance' => 4.8],
        ['zone' => 'Zona Norte', 'name' => 'Campinho', 'distance' => 17.0],
        ['zone' => 'Zona Norte', 'name' => 'Cavalcanti', 'distance' => 11.4],
        ['zone' => 'Zona Norte', 'name' => 'Cascadura', 'distance' => 14.1],
        ['zone' => 'Zona Norte', 'name' => 'Coelho Neto', 'distance' => 15.6],
        ['zone' => 'Zona Norte', 'name' => 'Colégio', 'distance' => 14.6],
        ['zone' => 'Zona Norte', 'name' => 'Complexo do Alemão', 'distance' => 7.3],
        ['zone' => 'Zona Norte', 'name' => 'Cordovil', 'distance' => 10.0],
        ['zone' => 'Zona Norte', 'name' => 'Costa Barros', 'distance' => 19.2],
        ['zone' => 'Zona Norte', 'name' => 'Engenheiro Leal', 'distance' => 13.0],
        ['zone' => 'Zona Norte', 'name' => 'Engenho da Rainha', 'distance' => 10.3],
        ['zone' => 'Zona Norte', 'name' => 'Guadalupe', 'distance' => 21.6],
        ['zone' => 'Zona Norte', 'name' => 'Higienópolis', 'distance' => 6.1],
        ['zone' => 'Zona Norte', 'name' => 'Honório Gurgel', 'distance' => 18.2],
        ['zone' => 'Zona Norte', 'name' => 'Irajá', 'distance' => 14.5],
        ['zone' => 'Zona Norte', 'name' => 'Jardim América', 'distance' => 15.0],
        ['zone' => 'Zona Norte', 'name' => 'Madureira', 'distance' => 15.3],
        ['zone' => 'Zona Norte', 'name' => 'Marechal Hermes', 'distance' => 18.8],
        ['zone' => 'Zona Norte', 'name' => 'Manguinhos', 'distance' => 5.7],
        ['zone' => 'Zona Norte', 'name' => 'Oswaldo Cruz', 'distance' => 17.6],
        ['zone' => 'Zona Norte', 'name' => 'Olaria', 'distance' => 6.3],
        ['zone' => 'Zona Norte', 'name' => 'Parada de Lucas', 'distance' => 12.3],
        ['zone' => 'Zona Norte', 'name' => 'Parque Colúmbia', 'distance' => 15.6],
        ['zone' => 'Zona Norte', 'name' => 'Pavuna', 'distance' => 19.3],
        ['zone' => 'Zona Norte', 'name' => 'Penha', 'distance' => 6.4],
        ['zone' => 'Zona Norte', 'name' => 'Penha Circular', 'distance' => 8.8],
        ['zone' => 'Zona Norte', 'name' => 'Quintino Bocaiuva', 'distance' => 13.2],
        ['zone' => 'Zona Norte', 'name' => 'Ramos', 'distance' => 4.4],
        ['zone' => 'Zona Norte', 'name' => 'Ricardo de Albuquerque', 'distance' => 22.7],
        ['zone' => 'Zona Norte', 'name' => 'Rocha Miranda', 'distance' => 16.2],
        ['zone' => 'Zona Norte', 'name' => 'Tomás Coelho', 'distance' => 10.5],
        ['zone' => 'Zona Norte', 'name' => 'Turiaçu', 'distance' => 16.4],
        ['zone' => 'Zona Norte', 'name' => 'Vaz Lobo', 'distance' => 14.5],
        ['zone' => 'Zona Norte', 'name' => 'Vicente de Carvalho', 'distance' => 12.3],
        ['zone' => 'Zona Norte', 'name' => 'Vigário Geral', 'distance' => 14.1],
        ['zone' => 'Zona Norte', 'name' => 'Vila da Penha', 'distance' => 11.7],
        ['zone' => 'Zona Norte', 'name' => 'Vila Kosmos', 'distance' => 12.6],
        ['zone' => 'Zona Norte', 'name' => 'Vista Alegre', 'distance' => 13.3],
        ['zone' => 'Baixada', 'name' => 'Belford Roxo', 'distance' => 25.5],
        ['zone' => 'Baixada', 'name' => 'Duque de Caxias', 'distance' => 13.5],
        ['zone' => 'Baixada', 'name' => 'Guapimirim', 'distance' => 66.1],
        ['zone' => 'Baixada', 'name' => 'Itaguai', 'distance' => 63.3],
        ['zone' => 'Baixada', 'name' => 'Japeri', 'distance' => 68.2],
        ['zone' => 'Baixada', 'name' => 'Magé', 'distance' => 52.7],
        ['zone' => 'Baixada', 'name' => 'Mesquita', 'distance' => 28.2],
        ['zone' => 'Baixada', 'name' => 'Nilópolis', 'distance' => 27.1],
        ['zone' => 'Baixada', 'name' => 'Nova Iguaçu', 'distance' => 31.0],
        ['zone' => 'Baixada', 'name' => 'Paracambi', 'distance' => 74.6],
        ['zone' => 'Baixada', 'name' => 'Queimados', 'distance' => 45.1],
        ['zone' => 'Baixada', 'name' => 'São João de Meriti', 'distance' => 19.5],
        ['zone' => 'Baixada', 'name' => 'Seropédica', 'distance' => 61.4],
        ['zone' => 'Grande Niterói', 'name' => 'Região Oceânica (Niterói)', 'distance' => 32.5],
        ['zone' => 'Grande Niterói', 'name' => 'Centro (Niterói)', 'distance' => 20.9],
        ['zone' => 'Grande Niterói', 'name' => 'São Gonçalo', 'distance' => 29.2],
        ['zone' => 'Grande Niterói', 'name' => 'Maricá', 'distance' => 61.3],
        ['zone' => 'Grande Niterói', 'name' => 'Itaboraí', 'distance' => 51.9],
        ['zone' => 'Grande Niterói', 'name' => 'Tanguá', 'distance' => 66.9],
        ['zone' => 'Grande Niterói', 'name' => 'Rio Bonito', 'distance' => 77.9]
    ];

    return $faker->randomElement($neighborhoods);
});

$factory->define(User::class, function (Faker\Generator $faker) {
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
        'id_ufrj' => $faker->cpf(false),
        'profile_pic_url' => $faker->imageUrl(500, 500, 'people'),
        'face_id' => NULL
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
        'myzone' => $neighborhood->zone,
        'neighborhood' => $neighborhood->name,
        'going' => $going,
        'place' => $faker->streetName,
        'route' => $faker->streetName . ', ' . $faker->streetName . ', ' . $faker->streetName,
        'description' => $faker->realText(100),
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
        'body' => $faker->realText(50),
        'created_at' => $faker->dateTime()
    ];
});
