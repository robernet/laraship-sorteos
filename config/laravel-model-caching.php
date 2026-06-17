<?php

return [
    'disabled' => env('LARAVEL_MODEL_CACHING_DISABLED', false),
    'store' => env('LARAVEL_MODEL_CACHING_STORE', null),
    'cache-prefix' => env('LARAVEL_MODEL_CACHING_PREFIX', null),
];
