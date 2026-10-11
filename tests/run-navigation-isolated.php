<?php

require dirname(__DIR__).'/vendor/autoload.php';
$environment = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__).'/.env'));
if (($environment['APP_ENV'] ?? '') !== 'testing' || ($environment['DB_DATABASE'] ?? '') !== 'logigate_testing'
    || ! in_array($environment['DB_HOST'] ?? '', ['127.0.0.1', 'localhost'], true)
    || ! empty($environment['DB_URL'])) {
    throw new RuntimeException('Refusing tests outside confirmed local services.');
}
$database = 'logigate_testing_v1_'.bin2hex(random_bytes(5));
$pdo = new PDO('mysql:host='.$environment['DB_HOST'].';port='.($environment['DB_PORT'] ?? 3306), $environment['DB_USERNAME'], $environment['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
register_shutdown_function(function () use ($pdo, $database) {
    if (preg_match('/^logigate_testing_v1_[a-f0-9]{10}$/', $database)) {
        $pdo->exec('DROP DATABASE `'.$database.'`');
    }
});
// Clone structure only. No application rows, migrations, seeders or foreign services.
$tables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'logigate_testing' AND TABLE_TYPE = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    if (! preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        throw new RuntimeException('Unexpected table identifier.');
    }
    $pdo->exec('CREATE TABLE `'.$database.'`.`'.$table.'` LIKE `logigate_testing`.`'.$table.'`');
}
$environment['DB_DATABASE'] = $database;
$environment['SESSION_DRIVER'] = $environment['CACHE_STORE'] = $environment['MAIL_MAILER'] = 'array';
$environment['QUEUE_CONNECTION'] = 'sync';
foreach ($environment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
putenv('V1_TEST_DATABASE='.$database);
unset($environment);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.connections.mysql.database') !== $database) {
    throw new RuntimeException('Cached configuration points outside sandbox.');
}
(require dirname(__DIR__).'/database/migrations/2026_10_10_120000_add_group_code_to_menus.php')->up();
(require dirname(__DIR__).'/database/migrations/2026_10_10_150000_harden_subscription_billing.php')->up();
echo "Disposable schema prepared; application rows untouched.\n";
restore_error_handler();
restore_exception_handler();
$_SERVER['argv'] = [$argv[0], 'tests/Feature/NavigationIntegrationTest.php'];
require dirname(__DIR__).'/vendor/bin/phpunit';
