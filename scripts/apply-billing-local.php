<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__).'/vendor/autoload.php';
$environment = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__).'/.env'));
if (($environment['APP_ENV'] ?? '') !== 'testing' || ($environment['DB_DATABASE'] ?? '') !== 'logigate_testing'
    || ! in_array($environment['DB_HOST'] ?? '', ['127.0.0.1', 'localhost'], true) || ! empty($environment['DB_URL'])) {
    throw new RuntimeException('Migration restricted to confirmed local services.');
}
unset($environment);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (config('database.connections.mysql.database') !== 'logigate_testing'
    || ! in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost'], true)
    || config('database.connections.mysql.url')) {
    throw new RuntimeException('Unexpected cached connection.');
}

$protected = [
    'planos' => Schema::getColumnListing('planos'),
    'plano_modulos' => Schema::getColumnListing('plano_modulos'),
    'activated_modules' => Schema::getColumnListing('activated_modules'),
    'subscricoes' => array_values(array_diff(Schema::getColumnListing('subscricoes'), ['commercial_snapshot', 'renewal_notified_at'])),
    'pagamento_onlines' => Schema::getColumnListing('pagamento_onlines'),
];
$fingerprints = function () use ($protected) {
    $result = [];
    foreach ($protected as $table => $columns) {
        $rows = DB::table($table)->select($columns)->orderBy('id')->get();
        $result[$table] = ['rows' => $rows->count(), 'sha256' => hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR))];
    }

    return $result;
};
$before = $fingerprints();
$exitCode = Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_10_150000_harden_subscription_billing.php', '--force' => true]);
if ($exitCode !== 0) {
    throw new RuntimeException('Billing migration failed. Review migration output locally.');
}
$after = $fingerprints();
if ($before !== $after) {
    throw new RuntimeException('Commercial data changed unexpectedly. Stop and inspect local backup/history.');
}
echo json_encode(['billing_migration' => 'applied', 'commercial_data_unchanged' => true, 'rows' => array_map(fn ($item) => $item['rows'], $after)], JSON_PRETTY_PRINT)."\n";
