<?php

require dirname(__DIR__).'/vendor/autoload.php';
$env = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__).'/.env'));
if (($env['DB_DATABASE'] ?? '') !== 'logigate_testing' || ! in_array($env['DB_HOST'] ?? '', ['localhost', '127.0.0.1'], true) || ! empty($env['DB_URL'])) {
    throw new RuntimeException('Only the confirmed local database may be inspected.');
}
unset($env);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.connections.mysql.database') !== 'logigate_testing' || ! in_array(config('database.connections.mysql.host'), ['localhost','127.0.0.1'], true) || config('database.connections.mysql.url')) throw new RuntimeException('Unexpected cached connection.');
$report = ['timezone' => config('app.timezone'), 'database_version' => Illuminate\Support\Facades\DB::selectOne('SELECT VERSION() AS version')->version];
foreach (['processos','licenciamentos','licenciamento_processos','proc_licen_sales','customers','customers_empresas','regiao_aduaneiras','tipo_transportes','estancias','paises','portos'] as $table) {
    $report[$table] = Illuminate\Support\Facades\Schema::getColumns($table);
}
$report['process_states'] = Illuminate\Support\Facades\DB::table('processos')->selectRaw('Estado, COUNT(*) AS total')->groupBy('Estado')->get();
$report['license_types'] = Illuminate\Support\Facades\DB::table('licenciamentos')->selectRaw('tipo_declaracao, COUNT(*) AS total')->groupBy('tipo_declaracao')->get();
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
