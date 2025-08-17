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

    // Domain atau origin yang diizinkan
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // HTTP methods yang diizinkan
    'allowed_methods' => ['*'], // ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']

    // Domain asal (origin) yang diizinkan
    'allowed_origins' => ['*'], // ['https://example.com']

    // Support wildcard origin
    'allowed_origins_patterns' => [],

    // Header yang boleh dikirimkan oleh client
    'allowed_headers' => ['*'],

    // Header yang boleh dikirimkan kembali oleh server
    'exposed_headers' => [],

    // Lama waktu preflight request (OPTIONS) di-cache browser
    'max_age' => 0,

    // Apakah mengizinkan cookie atau credentials
    'supports_credentials' => false,

];
