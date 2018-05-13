<?php

return [
    'default' => 'local',
    'cloud' => 's3',
    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'user_content' => [
            'driver' => 's3',
            'url' => 'https://s3.amazonaws.com/usercontent.caronae',
            'visibility' => 'public',
            'bucket' => 'usercontent.caronae',
            'region' => 'us-east-1',
            'key' => null,
            'secret' => null,
        ],

        's3' => [
            'driver' => 's3',
            'key' => null,
            'secret' => null,
            'region' => 'us-east-1',
            'bucket' => 'caronae',
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
