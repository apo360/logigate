<?php

namespace App\Livewire;

use App\Application\Integracoes\Services\IntegracaoResolverService;
use App\Livewire\Concerns\RequiresActiveEmpresa;
use App\Models\Menu;
use App\Models\Module;
use App\Models\PlanoModulo;
use App\Models\Subscricao;
use App\Support\MenuTree;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MenuDinamico extends Component
{
    use RequiresActiveEmpresa;

    public $modulosAtivos = [];

    public $menusPrincipais = [];

    public $menusPorModulo = [];

    public $nomesModulos = [];

    public $facturacaoHongayetuActiva = false;

    public function mount()
    {
        $empresa = TenantContext::empresa();
        if (! $empresa) {
            $this->menusPrincipais = [];

            return;
        }

        // 1) Buscar planos ativos
        $planosAtivos = $empresa->subscricoes()
            ->whereIn('status', Subscricao::activeStatuses())
            ->where(function ($query) {
                $query->whereNull('data_expiracao')
                    ->orWhere('data_expiracao', '>', now());
            })
            ->pluck('plano_id');

        // 2) Módulos ativos
        $this->modulosAtivos = PlanoModulo::whereIn('plano_id', $planosAtivos)
            ->distinct()
            ->pluck('modulo_id')
            ->toArray();

        // 3) Buscar menus Eloquent
        $menus = Menu::whereIn('module_id', $this->modulosAtivos)
            ->orderBy('order_priority')
            ->get()->filter(function ($m) {
                return ! $m->permission || Auth::user()?->can($m->permission);
            });

        // 4) Converter para uma estrutura básica de array
        $menusArr = $menus->map(function ($menu) {
            return [
                'id' => $menu->id,
                'parent_id' => $menu->parent_id,
                'module_id' => $menu->module_id,
                'menu_name' => $menu->menu_name,
                'route' => $menu->route,
                'icon' => $menu->icon,
                'children' => [],
            ];
        })->keyBy('id')->toArray();

        $this->menusPrincipais = MenuTree::build(array_values($menusArr));
        $this->menusPorModulo = collect($this->menusPrincipais)->groupBy('module_id')->map->all()->all();
        $this->nomesModulos = Module::whereIn('id', $this->modulosAtivos)->pluck('module_name', 'id')->all();

        // 7) Menus dinâmicos com integração Hongayetu Facturação
        $this->facturacaoHongayetuActiva = app(IntegracaoResolverService::class)->isFacturacaoHongayetuActiva($empresa?->id);

        // Permission-filtered trees are rebuilt against the current tenant, never cached.
    }

    public function render()
    {
        return view('livewire.menu-dinamico', [
            'facturacaoHongayetuActiva' => $this->facturacaoHongayetuActiva,
        ]);
    }
}
