<?php

return [
    'host' => env('VULNERAR_HOST', 'ingest.vulnerar.com'),

    'token' => env('VULNERAR_TOKEN'),

    'queue' => env('VULNERAR_QUEUE', null),
];