<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The Vue dev server runs on a different origin (port 5173) than the API
    | (port 8000), so browser requests are cross-origin and need these headers.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // SCRUM-540 : meme source que le lien de reinitialisation, pour que
        // les deux ne puissent pas diverger.
        config('app.frontend_url'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
