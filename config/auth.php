<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'sanctum'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'pegawai',
        ],
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'pegawai',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'pegawai' => [
            'driver' => 'eloquent',
            'model' => App\Models\Pegawai::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
