<?php

// config/hongayetu_facturacao.php

return [
    'api_url' => env('HONGAYETU_FACTURACAO_API_URL', 'https://api.hongayetu.com/logigate/v1'),
    'api_token' => env('HONGAYETU_FACTURACAO_API_TOKEN'),
    'api_key' => env('HONGAYETU_FACTURACAO_API_KEY'),
    'timeout' => env('HONGAYETU_FACTURACAO_TIMEOUT', 30),
    'retry' => env('HONGAYETU_FACTURACAO_RETRY', 1),
    'environment' => env('HONGAYETU_FACTURACAO_ENV', 'production'),
];
