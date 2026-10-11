<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Models\User;
use App\Services\EffectiveModuleResolver;
use App\Services\NavigationResolver;
use App\Support\CompanyRbac;
use App\Support\MenuTree;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CompareNavigation extends Command
{
    protected $signature = 'navigation:compare {empresa} {user}';
    protected $description = 'Comparação de navegação por empresa/utilizador, sem alterar acessos';

    public function handle(EffectiveModuleResolver $modules, NavigationResolver $navigation): int
    {
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        $actor = User::findOrFail($this->argument('user'));
        Auth::setUser($actor);
        $empresaId = (int) $this->argument('empresa');
        abort_unless(TenantContext::userBelongsToEmpresa($actor, $empresaId), 403);
        session()->put('empresa_id', $empresaId);
        CompanyRbac::activate();
        $snapshot = $modules->snapshot($empresaId);
        $oldModules = [];
        foreach ($snapshot['subscriptions'] as $sub) {
            if (empty($sub['deleted_at']) && in_array($sub['status'], ['ativa', 'ATIVA'], true)
                && (! $sub['data_expiracao'] || CarbonImmutable::parse($sub['data_expiracao'])->gt(now()))) {
                $oldModules = array_merge($oldModules, $snapshot['planModules'][$sub['plano_id']] ?? []);
            }
        }
        $rows = Menu::orderBy('order_priority')->orderBy('id')->get();
        $old = MenuTree::build($rows->filter(fn ($row) => in_array((int) $row->module_id, $oldModules, true) && (! $row->permission || $actor->can($row->permission)))->toArray());
        $new = $navigation->dynamic($actor, $modules->ids($empresaId));
        $flatten = function (array $tree) use (&$flatten): array {
            $result = [];
            foreach ($tree as $node) {
                $result[] = (int) $node['id'];
                $result = array_merge($result, $flatten($node['children'] ?? []));
            }
            return $result;
        };
        $oldIds = $flatten($old);
        $newIds = $flatten($new);
        $catalogue = \Illuminate\Support\Facades\DB::table('permissions')->where('guard_name', 'web')->pluck('name')->all();
        $issues = $rows->map(function ($row) use ($actor, $catalogue) {
            $permission = $row->permission ?: (config('navigation.permissions')[$row->route] ?? null);
            return ['menu_id' => $row->id, 'route_exists' => Route::has($row->route ?: '#'),
                'permission_mapped' => (bool) $permission,
                'permission_exists' => $permission ? in_array($permission, $catalogue, true) : null,
                'permission_allowed' => $permission ? \App\Support\BusinessAuthorization::allows($actor, $permission) : null];
        });
        $groups = $navigation->groups($actor, $new, false);
        $fixed = [];
        foreach ($groups as $code => $group) {
            foreach ($group['items'] as $node) {
                if (is_string($node['id']) && str_starts_with($node['id'], 'fixed:')) {
                    $fixed[] = ['id' => $node['id'], 'group' => $code, 'route' => $node['route']];
                }
            }
        }
        $this->line(json_encode(['added' => array_values(array_diff($newIds, $oldIds)),
            'removed' => array_values(array_diff($oldIds, $newIds)), 'modules' => $modules->resolve($empresaId),
            'menu_checks' => $issues, 'fixed_visible' => $fixed], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
