<?php

return [
    'name' => env('APP_NAME', 'Reisetagebuch'),
    'version' => env('APP_VERSION', '0.0.0'),
    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    'recent_location' => [
        'radius' => env('APP_RECENT_LOCATION_RADIUS', 200),
        'timeout' => env('APP_RECENT_LOCATION_TIMEOUT', 60 * 24),
    ],

    'nearby' => [
        'radius' => env('APP_NEARBY_RADIUS', 500),
    ],

    'registration' => env('APP_REGISTRATION', true),

    'invite' => [
        'enabled' => env('APP_INVITE_ENABLED', false),
        'whitelist' => env('APP_INVITE_WHITELIST', ''),
    ],
];
