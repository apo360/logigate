<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Module;
use App\Models\User;
use App\Support\BusinessAuthorization;
use App\Support\NavigationTree;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

final class NavigationResolver
{
    public function allowsRoute(User $actor, string $route): bool
    {
        $empresa = TenantContext::empresaId($actor);
        if (! $empresa || ! Route::has($route)) {
            return false;
        }
        $permission = config('navigation.permissions')[$route] ?? null;
        if ($permission && ! BusinessAuthorization::allows($actor, $permission)) {
            return false;
        }
        foreach (config('navigation.route_modules') as $pattern => $code) {
            if (Str::is($pattern, $route)) {
                $modules = app(EffectiveModuleResolver::class);
                $id = $modules->moduleId($code);
                return $id && in_array($id, $modules->ids($empresa), true);
            }
        }

        return true;
    }

    public function dynamic(User $actor, array $moduleIds): array
    {
        $names = Module::pluck('module_name', 'id')->all();
        $rows = Menu::orderBy('order_priority')->orderBy('id')->get()->map(function ($menu) use ($names) {
            $row = $menu->toArray();
            $row['group_code'] = $menu->group_code ?: config('navigation.module_groups')[$names[$menu->module_id] ?? ''] ?? null;
            return $row;
        })->all();

        return NavigationTree::filter($rows, function ($node) use ($actor, $moduleIds) {
            $route = $node['route'] ?? '#';
            if (empty($node['module_id']) || ! in_array((int) $node['module_id'], $moduleIds, true)
                || ! $route || $route === '#' || ! Route::has($route)) {
                return false;
            }
            $permission = $node['permission'] ?: (config('navigation.permissions')[$route] ?? null);

            return ! $permission || BusinessAuthorization::allows($actor, $permission);
        });
    }

    public function groups(User $actor, array $tree, bool $integration): array
    {
        if (! TenantContext::empresaId($actor)) {
            return [];
        }
        $fixed = [
            ['dashboard', 'Painel geral', 'fa fa-gauge', 'operations', null],
            ['leander.dashboard', 'Tarefas', 'fa fa-tasks', 'operations', null],
            ['relatorio.licenciamento', 'Relatórios', 'fa fa-file-pdf', 'operations', null],
            ['arquivos.index', 'Arquivos', 'fa fa-file-alt', 'operations', 'documents.view'],
            ['billing.plans', 'Plano e subscrição', 'fa fa-credit-card', 'administration', null],
        ];
        if ($integration) {
            $fixed[] = ['integracoes.facturacao-hongayetu.facturas', 'Facturas Hongayetu', 'fa fa-file-invoice', 'finance', null];
            $fixed[] = ['integracoes.facturacao-hongayetu.emitir-ft', 'Emitir FT manual', 'fa fa-plus-circle', 'finance', null];
        }
        $leading = [];
        foreach ($fixed as [$route, $label, $icon, $group, $permission]) {
            if (Route::has($route) && (! $permission || BusinessAuthorization::allows($actor, $permission))) {
                $node = ['id' => 'fixed:'.$route, 'parent_id' => null, 'route' => $route, 'menu_name' => $label, 'icon' => $icon, 'group_code' => $group, 'children' => []];
                if (in_array($route, ['dashboard', 'leander.dashboard'], true)) {
                    $leading[] = $node;
                } else {
                    $tree[] = $node;
                }
            }
        }
        $tree = array_merge($leading, $tree);
        $empresa = TenantContext::empresa($actor);
        foreach ([['usuarios.index', 'Equipa e permissões', 'fa fa-users', 'manageUsers'],
            ['admin.integracoes', 'Integrações', 'fa fa-plug', 'manageIntegrations']] as [$route, $label, $icon, $ability]) {
            if ($empresa && Route::has($route) && $actor->can($ability, $empresa)) {
                $tree[] = ['id' => 'fixed:'.$route, 'parent_id' => null, 'route' => $route,
                    'menu_name' => $label, 'icon' => $icon, 'group_code' => 'administration', 'children' => []];
            }
        }

        return NavigationTree::groups($tree, config('navigation.groups'), $actor->getRoleNames()->all());
    }
}
