<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__).'/vendor/autoload.php';
$environment = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__).'/.env'));
if (($environment['APP_ENV'] ?? '') !== 'testing' || ($environment['DB_DATABASE'] ?? '') !== 'logigate_testing'
    || ! in_array($environment['DB_HOST'] ?? '', ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('Refusing writes outside confirmed local testing services.');
}
$database = 'logigate_testing_v1_'.bin2hex(random_bytes(5));
$pdo = new PDO('mysql:host='.$environment['DB_HOST'].';port='.($environment['DB_PORT'] ?? 3306), $environment['DB_USERNAME'], $environment['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
register_shutdown_function(function () use ($pdo, $database) {
    if (preg_match('/^logigate_testing_v1_[a-f0-9]{10}$/', $database)) {
        $pdo->exec('DROP DATABASE `'.$database.'`');
    }
});
$environment['DB_DATABASE'] = $database;
$environment['SESSION_DRIVER'] = 'array';
$environment['CACHE_STORE'] = 'array';
$environment['MAIL_MAILER'] = 'array';
foreach ($environment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
putenv('V1_TEST_DATABASE='.$database);
unset($environment);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

Schema::create('modules', function (Blueprint $table) {
    $table->id();
    $table->string('module_name');
});
Schema::create('menus', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->unsignedBigInteger('module_id')->nullable();
    foreach (['menu_name', 'slug', 'route', 'icon', 'permission', 'description'] as $field) {
        $table->string($field)->nullable();
    }
    $table->integer('order_priority')->default(0);
    $table->timestamps();
});
echo "Disposable local test schema prepared; application data untouched.\n";
restore_error_handler();
restore_exception_handler();
$_SERVER['argv'] = [$argv[0], 'tests/Feature/LayoutCompatibilityTest.php'];
require dirname(__DIR__).'/vendor/bin/phpunit';
