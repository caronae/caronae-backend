<?php

return [
    'default' => 'local',
    'cloud' => 's3',
    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
        ],
        
        // used for Backpack/LogManager
        'storage' => [
            'driver' => 'local',
            'root'   => storage_path(),
        ],

        'backups' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'root' => 'backups'
        ],

    ],

];
