<?php

// Apply only this feature's migrations to the confirmed local testing database or its sandbox.
require dirname(__DIR__) . '/vendor/autoload.php';
$environment = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__) . '/.env'));
if (($environment['APP_ENV'] ?? '') !== 'testing'
    || ($environment['DB_DATABASE'] ?? '') !== 'logigate_testing'
    || !in_array($environment['DB_HOST'] ?? '', ['127.0.0.1', 'localhost'], true)
    || !empty($environment['DB_URL'])) {
    throw new RuntimeException('Expected confirmed local testing database.');
}
$target = $environment['DB_DATABASE'];
if (in_array('--sandbox', $argv, true)) {
    $manifest = json_decode(file_get_contents(dirname(__DIR__) . '/tests/.v1-sandbox.json'), true, flags: JSON_THROW_ON_ERROR);
    $target = $manifest['database'] ?? '';
    if (!preg_match('/^logigate_testing_v1_[a-f0-9]{10}$/', $target)) {
        throw new RuntimeException('Invalid sandbox database.');
    }
}
foreach (['APP_ENV', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
    $value = $key === 'DB_DATABASE' ? $target : ($environment[$key] ?? null);
    if ($value !== null) {
        putenv($key . '=' . $value);
        $_ENV[$key] = $_SERVER[$key] = $value;
    }
}
unset($environment);
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
if (!app()->environment('testing') || DB::connection()->getDatabaseName() !== $target
    || !in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost'], true)
    || config('database.connections.mysql.url')) {
    throw new RuntimeException('Unexpected effective database connection.');
}
$columns = array_values(array_diff(Schema::getColumnListing('processos'), ['natureza_operacao']));
$fingerprint = fn () => hash('sha256', json_encode(DB::table('processos')->select($columns)->orderBy('id')->get(), JSON_THROW_ON_ERROR));
$before = $fingerprint();
$menus = DB::table('menus')->orderBy('id')->pluck('id')->all();
foreach (['2026_10_17_100000_add_natureza_operacao_to_processos.php', '2026_10_17_100001_link_existing_exportacao_menu.php'] as $file) {
    $exit = Artisan::call('migrate', ['--path' => 'database/migrations/' . $file]);
    if ($exit !== 0) {
        throw new RuntimeException('Migration failed: ' . $file);
    }
}
if ($before !== $fingerprint() || $menus !== DB::table('menus')->orderBy('id')->pluck('id')->all()) {
    throw new RuntimeException('Unexpected change to existing process data or menu identities.');
}
echo json_encode([
    'database' => $target,
    'process_data_preserved' => true,
    'menu_ids_preserved' => true,
    'natureza_column' => Schema::hasColumn('processos', 'natureza_operacao'),
    'barris_type' => Schema::getColumnType('processos', 'quantidade_barris'),
    'exportacao_menu' => DB::table('menus')->where('menu_name', 'Exportação')->get(['id', 'menu_name', 'route']),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
