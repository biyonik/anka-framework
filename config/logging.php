<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | Varsayılan olarak kullanılacak log kanalı.
    |
    */
    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Kullanılabilir log kanallarının yapılandırması.
    |
    */
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'stderr'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/app.log'),
            'level' => 'debug',
            'formatter' => 'line',
            'locking' => false,
            'processors' => [
                'introspection' => true,
                'web' => true,
                'memory' => false,
            ],
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/app.log'),
            'level' => 'debug',
            'days' => 14,
            'formatter' => 'line',
            'processors' => [
                'introspection' => true,
                'web' => true,
            ],
        ],

        'stderr' => [
            'driver' => 'stream',
            'url' => 'php://stderr',
            'formatter' => 'line',
            'level' => 'debug',
        ],

        'syslog' => [
            'driver' => 'syslog',
            'ident' => env('APP_NAME', 'app'),
            'facility' => LOG_USER,
            'formatter' => 'line',
            'level' => 'debug',
        ],

        'json' => [
            'driver' => 'single',
            'path' => storage_path('logs/json.log'),
            'level' => 'debug',
            'formatter' => 'json',
            'processors' => [
                'introspection' => true,
                'web' => true,
                'memory' => true,
            ],
        ],
    ],
];