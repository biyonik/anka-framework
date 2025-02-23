<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Sisteminizin varsayılan disk sürücüsünü buradan ayarlayabilirsiniz.
    | Desteklenen sürücüler: "local", "ftp"
    |
    */
    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Burada istediğiniz kadar filesystem disk konfigürasyonu tanımlayabilirsiniz.
    | Her disk için farklı sürücü ve ayar kullanabilirsiniz.
    |
    */
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
        ],

        'private' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
        ],

        'ftp' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            'port' => env('FTP_PORT', 21),
            'ssl' => env('FTP_SSL', false),
            'passive' => env('FTP_PASSIVE', true),
            'timeout' => env('FTP_TIMEOUT', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Burada public dizininizde oluşturulacak sembolik linkleri
    | tanımlayabilirsiniz. Her anahtar hedef yolu, değer ise kaynak
    | dizini temsil eder.
    |
    */
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];