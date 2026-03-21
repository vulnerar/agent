<?php

return [
    'token' => env('VULNERAR_TOKEN'),

    'host' => env('VULNERAR_HOST', 'ingest.vulnerar.com'),

    // Development Mode
    'dev' => env('VULNERAR_DEV', false),

    'agent' => [
        'port' => env('VULNERAR_AGENT_PORT', 2709),
        'timeout' => 0.5,
        'buffer' => 100,
    ]
];