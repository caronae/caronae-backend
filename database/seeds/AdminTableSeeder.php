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
                'email' => '1@1.com',
                'password' => bcrypt('1234')
            ],
        ];

        foreach ($admins as $admin) {
            Admin::create($admin);
        };
    }
}