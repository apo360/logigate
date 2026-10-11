<?php

// Explicit local rollout. Default: read-only comparison and reconciliation simulation.
require dirname(__DIR__).'/vendor/autoload.php';
$environment = Dotenv\Dotenv::parse(file_get_contents(dirname(__DIR__).'/.env'));
if (($environment['APP_ENV'] ?? '') !== 'testing' || ($environment['DB_DATABASE'] ?? '') !== 'logigate_testing'
    || ! in_array($environment['DB_HOST'] ?? '', ['127.0.0.1', 'localhost'], true)
    || ! empty($environment['DB_URL'])) {
    throw new RuntimeException('This rollout is restricted to the confirmed local application.');
}
unset($environment);
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (config('database.connections.mysql.database') !== 'logigate_testing'
    || ! in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost'], true)
    || config('database.connections.mysql.url')) {
    throw new RuntimeException('Cached connection is outside the approved local scope.');
}
config(['session.driver' => 'array', 'cache.default' => 'array']);
$apply = in_array('--apply', $argv, true);
$directory = storage_path('app/navigation-rollout');
if (! is_dir($directory)) {
    mkdir($directory, 0777, true);
}
$report = ['scope' => 'Confirmed local application only', 'apply' => $apply];
$report['catalogue'] = Illuminate\Support\Facades\DB::table('modules')->select('id', 'module_name')->get()->all();
$report['menus'] = Illuminate\Support\Facades\DB::table('menus')->select('id', 'route', 'module_id')->get()->all();
$memberships = Illuminate\Support\Facades\DB::table('empresa_users')->select('empresa_id', 'user_id')->distinct()->get();
foreach ($memberships as $membership) {
    $key = substr(hash('sha256', $membership->empresa_id.':'.$membership->user_id), 0, 12);
    Illuminate\Support\Facades\Artisan::call('navigation:compare', ['empresa' => $membership->empresa_id, 'user' => $membership->user_id]);
    $report['scenarios'][$key] = json_decode(Illuminate\Support\Facades\Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
}
$service = app(App\Services\ModuleReconciliation::class);
$companies = Illuminate\Support\Facades\DB::table('activated_modules')->distinct()->pluck('empresa_id');
foreach ($companies as $empresa) {
    $manifest = $service->preview((int) $empresa);
    $path = $directory.'/reconciliation-'.$empresa.'.json';
    file_put_contents($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    $entry = ['proven' => count($manifest['changes']), 'review' => count($manifest['review'])];
    if ($apply) {
        $archive = $directory.'/approved-'.$empresa.'-'.hash('sha256', json_encode($manifest, JSON_THROW_ON_ERROR)).'.json';
        if (! file_exists($archive)) {
            file_put_contents($archive, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        }
        $entry['applied'] = $service->apply($manifest);
    }
    $report['reconciliation'][$empresa] = $entry;
}
if ($apply) {
    $exit = Illuminate\Support\Facades\Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_10_10_120000_add_group_code_to_menus.php', '--force' => true,
    ]);
    if ($exit !== 0) {
        throw new RuntimeException('Migration failed; inspect Laravel diagnostics.');
    }
    $report['migration'] = trim(Illuminate\Support\Facades\Artisan::output());
}
file_put_contents($directory.'/report-'.($apply ? 'applied' : 'preview').'.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
$summary = ['scenarios' => count($report['scenarios'] ?? []), 'reconciliation' => $report['reconciliation'] ?? [], 'apply' => $apply];
echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
