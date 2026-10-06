<?php

require dirname(__DIR__).'/vendor/autoload.php';
$environment = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__).'/.env'));
if (($environment['APP_ENV'] ?? '') !== 'testing' || ($environment['DB_DATABASE'] ?? '') !== 'logigate_testing'
    || !in_array($environment['DB_HOST'] ?? '', ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('Refusing writes outside confirmed local testing services.');
}
$database = 'logigate_testing_v1_'.bin2hex(random_bytes(5));
$pdo = new PDO('mysql:host='.$environment['DB_HOST'].';port='.($environment['DB_PORT'] ?? 3306), $environment['DB_USERNAME'], $environment['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$environment['DB_DATABASE'] = $database;
$environment['SESSION_DRIVER'] = 'array'; $environment['CACHE_STORE'] = 'array'; $environment['MAIL_MAILER'] = 'array';
foreach ($environment as $key => $value) { putenv($key.'='.$value); $_ENV[$key] = $_SERVER[$key] = $value; }
putenv('V1_TEST_DATABASE='.$database);
unset($environment, $pdo);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
Schema::create('empresas', function (Blueprint $t) { $t->id(); $t->string('Designacao'); $t->boolean('ativo')->default(true); });
Schema::create('pauta_aduaneira', function (Blueprint $t) {
    $t->id(); $t->string('codigo'); $t->text('descricao');
    foreach(['uq','rg','sadc','ua','requisitos','observacao','iva','ieq'] as $field) $t->string($field)->nullable();
    $t->timestamps();
});
Schema::create('processos', function (Blueprint $t) {
    $t->id(); $t->unsignedBigInteger('empresa_id'); $t->unsignedBigInteger('customer_id')->nullable();
    $t->string('Estado'); $t->date('DataAbertura')->nullable(); $t->date('DataFecho')->nullable(); $t->softDeletes();
});
Schema::create('mercadorias', function (Blueprint $t) {
    $t->id(); $t->unsignedBigInteger('Fk_Importacao')->nullable(); $t->unsignedBigInteger('licenciamento_id')->nullable();
    $t->unsignedBigInteger('pauta_aduaneira_id')->nullable(); $t->string('codigo_pautal_snapshot')->nullable(); $t->timestamp('pauta_snapshot_at')->nullable();
});
(require dirname(__DIR__).'/database/migrations/2026_10_05_000001_create_marketplace_tables.php')->up();
fwrite(STDOUT, "Disposable local MySQL schema prepared; no application data modified.\n");
// PHPUnit owns its handlers; the temporary schema bootstrap must release them.
restore_error_handler();
restore_exception_handler();
$_SERVER['argv'] = [$argv[0], 'tests/Feature/MarketplaceExperienceTest.php', 'tests/Feature/MarketplacePageTest.php', 'tests/Feature/PublicPautaTest.php'];
require dirname(__DIR__).'/vendor/bin/phpunit';
