<?php

return [
    'default' => 'local',
    'cloud' => 'user_content',
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

        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
        ],

        'backups' => [
            'driver' => 's3',
            'key' => null,
            'secret' => null,
            'region' => 'us-east-1',
            'bucket' => 'backups.caronae',
            'root' => '',
        ],

    ],

];
