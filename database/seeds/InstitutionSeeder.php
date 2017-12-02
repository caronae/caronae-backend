<?php

use Caronae\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    protected $faker;

    public function __construct(Faker\Generator $faker)
    {
        $this->faker = $faker;
    }

    public function run()
    {
        DatabaseSeeder::emptyTable('institutions');

        Institution::create(['name' => 'UFRJ', 'authentication_url' => $this->faker->url]);
        Institution::create(['name' => 'UNIRIO', 'authentication_url' => $this->faker->url]);

        echo "Created institutions.\n";
    }
}