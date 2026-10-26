<?php

return [
    'ingest_token' => env('ANALYTICS_INGEST_TOKEN', 'dev-ingest-token'),
    'read_token'   => env('ANALYTICS_READ_TOKEN',   'dev-read-token'),
    'public_token' => env('ANALYTICS_PUBLIC_TOKEN', 'dev-public-token'),
    'ingest_url'   => env('ANALYTICS_INGEST_URL',   '/api/events'),
];