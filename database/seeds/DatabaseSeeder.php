<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        if (App::environment() == 'production') {
            throw new Exception('Seeder executado em ambiente de produção.');
        }

        //$this->setForeignKeyChecks(false);

        $this->call(NeighborhoodSeeder::class);
        $this->call(AdminTableSeeder::class);
        $this->call(UserRideTableSeeder::class);

        //$this->setForeignKeyChecks(true);
    }

    public function setForeignKeyChecks($value)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS='. ($value ? 1 : 0) . ';');
    }

    public static function emptyTable($tableName)
    {
        DB::statement("TRUNCATE $tableName CASCADE");
    }
}
