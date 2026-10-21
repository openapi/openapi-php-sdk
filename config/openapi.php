<?php

return [
    'username' => env('OPENAPI_OAUTH_USERNAME'),
    'apikey' => env('OPENAPI_OAUTH_APIKEY'),
    'test' => env('OPENAPI_OAUTH_TEST', false),

    'token' => [
        /*
         | Cache store used to share OAuth tokens between processes.
         | null = the application's default store (CACHE_STORE / CACHE_DRIVER).
         | Under Laravel Octane use a store shared across workers that supports atomic
         | locks: redis, memcached or database. "array" is per-process.
         */
        'store' => env('OPENAPI_CACHE_STORE'),

        'prefix' => env('OPENAPI_CACHE_PREFIX', 'openapi:token:'),

        // Seconds before expiry at which a token starts being refreshed. While one worker
        // refreshes it, the others keep using the still-valid token. Capped at half of the
        // token's lifetime so short-lived tokens are still cached.
        'refresh_skew' => 300,

        // Maximum lifetime of the refresh lock (safety net if a worker dies mid-request).
        // Keep it above the OAuth request timeout (30 seconds).
        'lock_seconds' => 45,

        // How long a worker waits for another worker's token request when no token exists.
        'lock_wait' => 15,
    ],
];
