<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            NeighborhoodSeeder::class,
            InstitutionSeeder::class,
            UserRideTableSeeder::class,
            AdminTableSeeder::class,
        ]);
    }

    public static function emptyTable($tableName)
    {
        DB::statement("TRUNCATE $tableName CASCADE");
    }
}
