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
if (($argv[1] ?? '') === '--v1-sandbox') {
    $manifest = json_decode(file_get_contents(__DIR__ . '/.v1-sandbox.json'), true, flags: JSON_THROW_ON_ERROR);
    if (! preg_match('/^logigate_testing_v1_[a-f0-9]{10}$/', $manifest['database'] ?? '')) { throw new RuntimeException('Invalid sandbox manifest.'); }
    $environment['DB_DATABASE'] = $manifest['database'];
    putenv('V1_TEST_DATABASE=' . $manifest['database']);
    array_splice($argv, 1, 1);
    $_SERVER['argv'] = $argv;
}
foreach (['APP_ENV', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
    if (isset($environment[$key])) {
        putenv($key . '=' . $environment[$key]);
        $_ENV[$key] = $_SERVER[$key] = $environment[$key];
    }
}
unset($environment);
require dirname(__DIR__) . '/vendor/bin/phpunit';
