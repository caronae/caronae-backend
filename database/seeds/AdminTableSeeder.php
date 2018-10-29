<?php

use Caronae\Models\Admin;
use Caronae\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminTableSeeder extends Seeder
{
    public function run()
    {
        DatabaseSeeder::emptyTable('admins');

        Admin::create([
            'name' => 'Fulana Silva',
            'email' => 'user@example.com',
            'password' => Hash::make('123456'),
            'user_id' => User::first()->id,
        ]);
    }
}
