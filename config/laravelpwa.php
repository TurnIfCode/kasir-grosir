<?php

return [
    'name' => 'LaravelPWA',
    'manifest' => [

        'name' => env('APP_NAME', 'Kasir Palugada'),

        'short_name' => 'Kasir',

        'start_url' => '/',

        'background_color' => '#ffffff',

        'theme_color' => '#0d6efd',

        'display' => 'standalone',

        'orientation'=> 'portrait',

        'status_bar'=> 'default',

        'icons' => [

            '72x72' => [
                'path' => '/images/icons/icon-72x72.png'
            ],

            '96x96' => [
                'path' => '/images/icons/icon-96x96.png'
            ],

            '128x128' => [
                'path' => '/images/icons/icon-128x128.png'
            ],

            '144x144' => [
                'path' => '/images/icons/icon-144x144.png'
            ],

            '152x152' => [
                'path' => '/images/icons/icon-152x152.png'
            ],

            '192x192' => [
                'path' => '/images/icons/icon-192x192.png'
            ],

            '384x384' => [
                'path' => '/images/icons/icon-384x384.png'
            ],

            '512x512' => [
                'path' => '/images/icons/icon-512x512.png'
            ],

        ]
    ]
];
