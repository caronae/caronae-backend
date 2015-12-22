<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Db::table('admins')->truncate();

        DB::table('admins')->insert([
            'name' => 'Administrador',
            'email' => '1@1.com',
            'password' => bcrypt('1234'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
