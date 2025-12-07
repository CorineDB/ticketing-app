<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter([
        'http://localhost:5173',
        'http://localhost:3000',
        env('FRONTEND_URL'),  // URL du frontend en production
    ]),

    'allowed_origins_patterns' => [
        '/^https:\/\/.*\.railway\.app$/',  // Tous les sous-domaines Railway
        '/^https:\/\/.*\.vercel\.app$/',   // Tous les sous-domaines Vercel
        '/^https:\/\/.*\.netlify\.app$/',  // Tous les sous-domaines Netlify
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
