<?php

return [
    'default' => env('BROADCAST_DRIVER', 'log'),

    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ],
        ],

        'websockets' => [
            'driver' => 'pusher',
            'key' => env('WEBSOCKETS_KEY', 'local'),
            'secret' => env('WEBSOCKETS_SECRET', 'secret'),
            'app_id' => env('WEBSOCKETS_APP_ID', 'local-app'),
            'options' => [
                'cluster' => 'mt1',
                'host' => env('WEBSOCKETS_HOST', '127.0.0.1'),
                'port' => env('WEBSOCKETS_PORT', 6001),
                'scheme' => env('WEBSOCKETS_SCHEME', 'http'),
                'useTLS' => env('WEBSOCKETS_SCHEME', 'http') === 'https',
            ],
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
];

