<?php

use App\Admin;
use App\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    public function run()
    {
        DatabaseSeeder::emptyTable('users');

        factory(User::class, 30)->create();
    }
}