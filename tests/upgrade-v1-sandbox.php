<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$env = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__) . '/.env'));
$manifest = json_decode(file_get_contents(__DIR__ . '/.v1-sandbox.json'), true, flags: JSON_THROW_ON_ERROR);
$name = $manifest['database'] ?? '';
if (! preg_match('/^logigate_testing_v1_[a-f0-9]{10}$/', $name) || ($env['DB_DATABASE'] ?? '') !== 'logigate_testing') { throw new RuntimeException('Unsafe sandbox.'); }
foreach ($env as $key => $value) { if (str_starts_with($key, 'DB_') || $key === 'APP_ENV') { putenv($key . '=' . ($key === 'DB_DATABASE' ? $name : $value)); $_ENV[$key] = $_SERVER[$key] = $key === 'DB_DATABASE' ? $name : $value; } }
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== $name) { throw new RuntimeException('Target mismatch.'); }
foreach (['2026_10_01_210001_add_data_partida_to_processos_table.php', '2026_10_01_210002_add_exchange_confirmation_to_processos_table.php', '2026_10_05_100001_add_import_results_to_migracaos.php', '2026_10_05_100002_add_operational_links_and_sequences.php'] as $file) {
    if ($file === '2026_10_01_210002_add_exchange_confirmation_to_processos_table.php' && Illuminate\Support\Facades\Schema::hasColumn('processos', 'cambio_confirmado')) { continue; }
    if ($file === '2026_10_05_100001_add_import_results_to_migracaos.php' && Illuminate\Support\Facades\Schema::hasColumn('migracaos', 'result')) { continue; }
    (require dirname(__DIR__) . '/database/migrations/' . $file)->up();
    echo 'Migration OK: ' . $file . PHP_EOL;
}
