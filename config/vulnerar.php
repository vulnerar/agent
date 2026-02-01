<?php

return [
    'host' => env('VULNERAR_HOST', 'ingest.vulnerar.com'),

    'key' => env('VULNERAR_KEY'),

    'queue' => env('VULNERAR_QUEUE', null),
];