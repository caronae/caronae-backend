<?php

return [

    'defaults' => [
        'guard' => 'api',
        'passwords' => 'admins',
    ],

    'guards' => [
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => Caronae\Models\Admin::class,
        ],
        'users' => [
            'driver' => 'eloquent',
            'model' => Caronae\Models\User::class,
        ],
    ],

    'passwords' => [
        'admins' => [
            'provider' => 'admins',
            'email' => 'auth.emails.password',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],
];
