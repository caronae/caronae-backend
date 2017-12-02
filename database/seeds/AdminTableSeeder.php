<?php

use Caronae\Models\Admin;
use Illuminate\Database\Seeder;

class AdminTableSeeder extends Seeder
{
    public function run()
    {
        DatabaseSeeder::emptyTable('admins');

        $admins = [
            [
                'name' => 'Administrador',
                'email' => 'user@example.com',
                'password' => '123456'
            ],
        ];

        foreach ($admins as $admin) {
            Admin::create($admin);
        };
    }
}