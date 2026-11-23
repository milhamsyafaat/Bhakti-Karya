<?php

return [
    'name'     => env('APP_NAME', 'Bhakti Karya'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url'      => rtrim(env('APP_URL', 'http://localhost'), '/'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    'session'  => env('SESSION_NAME', 'bk_session'),
];
