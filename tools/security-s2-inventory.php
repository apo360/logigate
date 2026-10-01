<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! $app->environment('testing') || config('database.connections.'.config('database.default').'.database') !== 'logigate_testing') {
    throw new RuntimeException('Isolated testing database required.');
}
$db = Illuminate\Support\Facades\DB::connection();
$result = ['environment' => $app->environment(), 'database' => $db->getDatabaseName(),
    'laravel' => $app->version(), 'spatie' => Composer\InstalledVersions::getPrettyVersion('spatie/laravel-permission')];
foreach (['roles','permissions','model_has_roles','model_has_permissions','role_has_permissions','empresa_users'] as $table) {
    $result['schema'][$table] = $db->select('SHOW CREATE TABLE `'.$table.'`');
}
$result['roles'] = $db->table('roles')->orderBy('id')->get();
$result['permission_catalog'] = $db->table('permissions')->select('id','name','guard_name')->orderBy('id')->get();
$roleNames = $db->table('roles')->where('guard_name','web')->pluck('name')->all();
foreach ($db->table('users')->orderBy('id')->pluck('id') as $id) {
    $memberships = $db->table('empresa_users')->where('user_id', $id)->select('empresa_id','role','conta')->get();
    $roles = $db->table('model_has_roles')->where('model_type', App\Models\User::class)->where('model_id', $id)->get();
    $permissions = $db->table('model_has_permissions')->where('model_type', App\Models\User::class)->where('model_id', $id)->get();
    $classification = count($memberships) === 1 ? 'SAFE_SINGLE_MEMBERSHIP' : (count($memberships) > 1 ? 'AMBIGUOUS_MULTI_MEMBERSHIP' : 'UNRESOLVED');
    if (count($memberships) > 0 && $memberships->every(fn ($m) => $m->role !== null && in_array($m->role, $roleNames, true))) {
        $classification = 'MAPPABLE_FROM_MEMBERSHIP_ROLE';
    }
    $flags = $db->table('users')->where('id',$id)->select('is_active','is_blocked')->first();
    $result['users'][] = compact('id','memberships','roles','permissions','classification','flags');
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
