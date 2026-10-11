<?php

require dirname(__DIR__).'/vendor/autoload.php';
$environment = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__).'/.env'));
if (($environment['APP_ENV'] ?? '') !== 'testing' || ($environment['DB_DATABASE'] ?? '') !== 'logigate_testing'
    || ! in_array($environment['DB_HOST'] ?? '', ['127.0.0.1', 'localhost'], true) || ! empty($environment['DB_URL'])) {
    throw new RuntimeException('Audit restricted to confirmed local services.');
}
unset($environment);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.connections.mysql.database') !== 'logigate_testing'
    || ! in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost'], true)
    || config('database.connections.mysql.url')) {
    throw new RuntimeException('Unexpected cached connection.');
}
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$report = ['scope' => 'Read-only local schema and aggregate counts; no personal or payment payload data'];
foreach (['planos', 'plano_modulos', 'plano_items', 'modules', 'subscricoes', 'activated_modules', 'pagamento_onlines', 'webhook_events', 'pagamentos', 'metodos_pagamento'] as $table) {
    $report['tables'][$table] = ['exists' => Schema::hasTable($table)];
    if (! Schema::hasTable($table)) continue;
    $report['tables'][$table]['rows'] = DB::table($table)->count();
    $report['tables'][$table]['columns'] = Schema::getColumnListing($table);
    $report['tables'][$table]['indexes'] = Schema::getIndexes($table);
    $report['tables'][$table]['foreign_keys'] = Schema::getForeignKeys($table);
}
foreach (['planos', 'subscricoes', 'pagamento_onlines'] as $table) {
    if (Schema::hasTable($table)) {
        $report['states'][$table] = DB::table($table)->select('status', DB::raw('COUNT(*) AS total'))->groupBy('status')->get()->all();
    }
}
$report['plan_prices'] = [];
foreach (['preco_mensal', 'preco_trimestral', 'preco_semestral', 'preco_anual'] as $column) {
    $report['plan_prices'][$column] = ['missing' => DB::table('planos')->whereNull($column)->count(), 'non_positive' => DB::table('planos')->where($column, '<=', 0)->count()];
}
$report['integrity'] = [
    'plans_without_modules' => DB::table('planos as p')->whereNotExists(fn ($q) => $q->selectRaw('1')->from('plano_modulos as pm')->whereColumn('pm.plano_id', 'p.id'))->count(),
    'payments_without_subscription' => DB::table('pagamento_onlines')->whereNull('subscription_id')->count(),
    'payment_company_mismatch' => DB::table('pagamento_onlines as p')->join('subscricoes as s', 's.id', '=', 'p.subscription_id')->whereColumn('p.empresa_id', '<>', 's.empresa_id')->count(),
    'active_subscriptions_without_plan' => DB::table('subscricoes')->whereIn('status', ['ativa', 'ATIVA'])->whereNull('plano_id')->count(),
    'unlinked_activations' => DB::table('activated_modules')->whereNull('subscricao_id')->count(),
];
$directory = storage_path('app/billing-audit');
if (! is_dir($directory)) mkdir($directory, 0777, true);
file_put_contents($directory.'/schema.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
echo json_encode(['rows' => array_map(fn ($table) => $table['rows'] ?? null, $report['tables']), 'states' => $report['states'],
    'plan_prices' => $report['plan_prices'], 'integrity' => $report['integrity']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
