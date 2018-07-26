<?php

use Caronae\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminTableSeeder extends Seeder
{
    public function run()
    {
        DatabaseSeeder::emptyTable('admins');

        Admin::create([
            'name' => 'Administrador',
            'email' => 'user@example.com',
            'password' => Hash::make('123456'),
        ]);
    }
}