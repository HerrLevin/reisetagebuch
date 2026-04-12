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

    'overpass' => [
        'radius' => env('APP_OVERPASS_RADIUS', 1000),
        'timeout' => env('APP_OVERPASS_TIMEOUT', 60 * 24 * 7),
        'url' => env('APP_OVERPASS_URL', 'https://overpass.private.coffee/api/interpreter'),
    ],

    'motis' => [
        'radius' => env('APP_MOTIS_RADIUS', 500),
        'single_location_radius' => env('APP_MOTIS_SINGLE_LOCATION_RADIUS', 100),
        'api_url' => env('APP_MOTIS_API_URL', 'https://api.transitous.org/api'),
    ],

    'nearby' => [
        'radius' => env('APP_NEARBY_RADIUS', 500),
    ],

    'registration' => env('APP_REGISTRATION', true),

    'invite' => [
        'enabled' => env('APP_INVITE_ENABLED', false),
        'whitelist' => env('APP_INVITE_WHITELIST', '') ? explode(',', env('APP_INVITE_WHITELIST')) : [],
    ],

    'transit' => [
        'refresh_interval' => env('APP_TRANSIT_REFRESH_INTERVAL', 5),
    ],

    'testing' => [
        'cypress' => env('APP_TESTING_CYPRESS', false),
        'cypress_token' => env('APP_TESTING_CYPRESS_TOKEN', 'testing'),
    ],
];
