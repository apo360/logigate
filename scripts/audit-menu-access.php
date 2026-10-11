<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['cache.default' => 'array', 'session.driver' => 'array']);
$report = ['scope' => 'Read-only schema and aggregate counts; no user/customer identifiers'];
foreach (['roles', 'model_has_roles', 'permissions', 'role_has_permissions', 'modules', 'menus', 'activated_modules', 'subscricoes', 'plano_modulos', 'plano_modulo'] as $table) {
    $exists = Schema::hasTable($table);
    $report['tables'][$table] = ['exists' => $exists];
    if ($exists) {
        $report['tables'][$table]['columns'] = Schema::getColumnListing($table);
        $report['tables'][$table]['rows'] = DB::table($table)->count();
    }
}
$report['role_names'] = DB::table('roles')->select('name')->distinct()->orderBy('name')->pluck('name')->all();
$report['configured_business_roles'] = config('rbac.roles');
$report['configured_permission_modules'] = config('rbac.permission_modules');
$report['menu_permission_counts'] = ['with_permission' => DB::table('menus')->whereNotNull('permission')->where('permission', '<>', '')->count(), 'total' => DB::table('menus')->count()];
if (Schema::hasColumn('model_has_roles', 'empresa_id')) {
    $report['role_assignments'] = ['legacy_unscoped' => DB::table('model_has_roles')->where(function ($q) {$q->whereNull('empresa_id')->orWhere('empresa_id', 0);})->count(), 'company_scoped' => DB::table('model_has_roles')->where('empresa_id', '>', 0)->count()];
}
$report['subscriptions'] = DB::table('subscricoes')->select('status', DB::raw('COUNT(*) AS total'))->groupBy('status')->get()->toArray();
if (Schema::hasColumn('activated_modules', 'subscricao_id')) {
    $report['activations'] = ['without_subscription' => DB::table('activated_modules')->whereNull('subscricao_id')->count(), 'with_subscription' => DB::table('activated_modules')->whereNotNull('subscricao_id')->count(), 'subscription_company_mismatch' => DB::table('activated_modules as a')->join('subscricoes as s', 's.id', '=', 'a.subscricao_id')->whereColumn('a.empresa_id', '<>', 's.empresa_id')->count()];
}
$report['module_catalogue'] = DB::table('modules')->select('id', 'parent_id', 'module_name')->orderBy('id')->get()->toArray();
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
