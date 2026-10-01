<?php

// Load local connection credentials into this process only, before PHPUnit XML.
require dirname(__DIR__) . '/vendor/autoload.php';

if (count($argv) < 2) {
    fwrite(STDERR, "Specify reviewed, non-destructive test files; do not run the entire suite.\n");
    exit(1);
}

$environment = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__) . '/.env'));
if (($environment['APP_ENV'] ?? null) !== 'testing'
    || ($environment['DB_DATABASE'] ?? null) !== 'logigate_testing') {
    fwrite(STDERR, "Refusing tests outside the prepared isolated database.\n");
    exit(1);
}
foreach (['APP_ENV', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
    if (isset($environment[$key])) {
        putenv($key . '=' . $environment[$key]);
        $_ENV[$key] = $_SERVER[$key] = $environment[$key];
    }
}
unset($environment);
require dirname(__DIR__) . '/vendor/bin/phpunit';
