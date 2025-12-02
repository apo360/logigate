<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PlanoModulo;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MenuDinamico extends Component
{
    public $modulosAtivos = [];
    public $menusPrincipais = [];

    public function mount()
    {
        $empresa = Auth::user()->empresas->first();
        if (!$empresa) {
            $this->menusPrincipais = [];
            return;
        }

        // 1) Buscar planos ativos
        $planosAtivos = $empresa->subscricoes()
            ->where('status', 'ATIVA')
            ->pluck('plano_id');

        // 2) Módulos ativos
        $this->modulosAtivos = PlanoModulo::whereIn('plano_id', $planosAtivos)
            ->pluck('modulo_id')
            ->toArray();

        // 3) Buscar menus Eloquent
        $menus = Menu::whereIn('module_id', $this->modulosAtivos)
            ->orderBy('order_priority')
            ->get()->filter(function ($m) {
                return !$m->permission || Auth::user()->can($m->permission);
            });

        // 4) Converter para uma estrutura básica de array
        $menusArr = $menus->map(function ($menu) {
            return [
                'id'        => $menu->id,
                'parent_id' => $menu->parent_id,
                'module_id' => $menu->module_id,
                'menu_name' => $menu->menu_name,
                'route'     => $menu->route,
                'icon'      => $menu->icon,
                'children'  => [],
            ];
        })->keyBy('id')->toArray();

        // 5) Construção da árvore
        $tree = [];

        foreach ($menusArr as $id => &$menu) {

            if ($menu['parent_id']) {
                // Inserir no pai
                $menusArr[$menu['parent_id']]['children'][] = &$menu;
            } else {
                // É menu principal
                $tree[] = &$menu;
            }
        }

        // 6) Atribuir ao componente
        $this->menusPrincipais = $tree;

        //
        $cacheKey = 'menus_user_' . Auth::id();

        $tree = Cache::remember($cacheKey, now()->addHours(6), function () use ($menusArr) {

            $tree = [];

            foreach ($menusArr as $id => &$menu) {

                if ($menu['parent_id']) {
                    $menusArr[$menu['parent_id']]['children'][] = &$menu;
                } else {
                    $tree[] = &$menu;
                }
            }

            return $tree;
        });
    }

    public function render()
    {
        return view('livewire.menu-dinamico');
    }
}
