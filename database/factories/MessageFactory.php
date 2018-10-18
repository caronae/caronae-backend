<?php

use Caronae\Models\Message;
use Faker\Generator as Faker;

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Message::class, function (Faker $faker) {
    return [
        'body' => $faker->text(50),
        'created_at' => $faker->dateTime()
    ];
});
