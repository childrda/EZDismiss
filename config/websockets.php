<?php

return [
    'apps' => [
        [
            'id' => env('WEBSOCKETS_APP_ID', 'local-app'),
            'name' => env('APP_NAME', 'CarLineManager'),
            'key' => env('WEBSOCKETS_KEY', 'local'),
            'secret' => env('WEBSOCKETS_SECRET', 'secret'),
            'capacity' => null,
            'enable_client_messages' => true,
            'enable_statistics' => true,
        ],
    ],

    'statistics' => [
        'model' => BeyondCode\LaravelWebSockets\Statistics\Models\WebSocketsStatisticsEntry::class,
        'interval_in_seconds' => 60,
        'delete_traces_older_than_days' => 60,
        'perform_dns_lookup' => false,
    ],

    'ssl' => [
        'local_cert' => env('WEBSOCKETS_SSL_LOCAL_CERT', null),
        'local_pk' => env('WEBSOCKETS_SSL_LOCAL_PK', null),
        'passphrase' => env('WEBSOCKETS_SSL_PASSPHRASE', null),
    ],

    'dashboard' => [
        'enabled' => env('WEBSOCKETS_DASHBOARD', true),
        'port' => env('WEBSOCKETS_DASHBOARD_PORT', 6002),
    ],

    'max_request_size_in_kb' => 250,
    'path' => env('WEBSOCKETS_PATH', 'websocket'),
    'middleware' => [
        'web',
    ],
];

