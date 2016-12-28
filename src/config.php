<?php

return [
    'debug' => env('DEBUG', false),

    /**
     * These config variables are mandatory
     */
    'ENDPOINT_URL'      => env('BSD_TOOLS_ENDPOINT_URL', null),
    'API_USER_ID'       => env('BSD_TOOLS_API_USER_ID', null),
    'API_USER_SECRET'   => env('BSD_TOOLS_API_USER_SECRET', null),

    /**
     * These config variables are optional
     */
    'DEFERRED_RESULT_MAX_ATTEMPTS'  => env('BSD_TOOLS_DEFERRED_RESULT_MAX_ATTEMPTS', 20),
    'DEFERRED_RESULT_INTERVAL'      => env('BSD_TOOLS_DEFERRED_RESULT_INTERVAL', 5),

    /**
     * For the machine API
     */
    'PRIVATE_API_URL'           => env('BSD_TOOLS_PRIVATE_API_URL', null),
    'PRIVATE_API_EMAIL'         => env('BSD_TOOLS_PRIVATE_API_EMAIL', null),
    'PRIVATE_API_PASSWORD'      => env('BSD_TOOLS_PRIVATE_API_PASSWORD', null),
    'PRIVATE_API_SESSION_ID'    => env('BSD_TOOLS_PRIVATE_API_SESSION_ID', null),
    'PRIVATE_API_CURL_DEBUG'    => env('BSD_TOOLS_PRIVATE_API_CURL_DEBUG', null)
];
