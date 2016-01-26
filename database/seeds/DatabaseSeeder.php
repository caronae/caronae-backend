<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        if (App::environment() == 'production') {
            throw new Exception('Seeder executado em ambiente de producao.');
        }

        //$this->setForeignKeyChecks(false);

        $this->call(BootstrapSeeder::class);
        $this->call(AdminTableSeeder::class);
        $this->call(UserTableSeeder::class);

        //$this->setForeignKeyChecks(true);

        Model::reguard();
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
