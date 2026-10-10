<?php

namespace App\Livewire;

use App\Models\Menu;
use App\Models\Module;
use App\Support\MenuTree;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class MenuBuilder extends Component
{
    public $menusTree = [];

    public $flatMenus = []; // for select parent

    public $modules = [];

    // form fields
    public $menuId;

    public $parent_id;

    public $module_id;

    public $menu_name;

    public $slug;

    public $order_priority = 0;

    public $route;

    public $icon;

    public $permission;

    public $description;

    public $showModal = false;

    protected $listeners = ['saveOrder' => 'saveOrder', 'refreshMenus' => 'loadMenus'];

    public function mount()
    {
        $this->authorizeUser();
        $this->loadMenus();
        $this->modules = Module::orderBy('module_name')->get()->toArray();
    }

    protected function authorizeUser()
    {
        // Ajusta conforme teu sistema de roles:
        if (! Auth::user()?->can('menus.manage')) {
            abort(403);
        }
    }

    public function loadMenus()
    {
        $this->authorizeUser();
        // Carrega menus ordenados e monta árvore
        $menusRaw = Menu::orderBy('order_priority')->get();

        // convert to arrays
        $menusArr = $menusRaw->map(function ($menu) {
            return [
                'id' => $menu->id,
                'parent_id' => $menu->parent_id,
                'module_id' => $menu->module_id,
                'menu_name' => $menu->menu_name,
                'slug' => $menu->slug,
                'order_priority' => $menu->order_priority,
                'route' => $menu->route,
                'icon' => $menu->icon,
                'permission' => $menu->permission,
                'description' => $menu->description,
                'children' => [],
            ];
        })->keyBy('id')->toArray();

        $this->menusTree = MenuTree::build(array_values($menusArr), true);
        $this->flatMenus = array_map(function ($m) {
            return [
                'id' => $m['id'],
                'menu_name' => $m['menu_name'],
                'parent_id' => $m['parent_id'],
            ];
        }, array_values($menusArr));
    }

    public function create()
    {
        $this->authorizeUser();
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->authorizeUser();
        $menu = Menu::findOrFail($id);
        $this->menuId = $menu->id;
        $this->parent_id = $menu->parent_id;
        $this->module_id = $menu->module_id;
        $this->menu_name = $menu->menu_name;
        $this->slug = $menu->slug;
        $this->order_priority = $menu->order_priority;
        $this->route = $menu->route;
        $this->icon = $menu->icon;
        $this->permission = $menu->permission;
        $this->description = $menu->description;
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->menuId = null;
        $this->parent_id = null;
        $this->module_id = null;
        $this->menu_name = null;
        $this->slug = null;
        $this->order_priority = 0;
        $this->route = null;
        $this->icon = null;
        $this->permission = null;
        $this->description = null;
        $this->resetValidation();
    }

    public function rules()
    {
        return [
            'menu_name' => ['required', 'string', 'max:191'],
            'module_id' => ['nullable', 'exists:modules,id'],
            'parent_id' => ['nullable', 'exists:menus,id'],
            'route' => ['nullable', 'string', 'max:191'],
            'icon' => ['nullable', 'string', 'max:191'],
            'permission' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'order_priority' => ['required', 'integer', 'min:0'],
        ];
    }

    public function save()
    {
        $this->authorizeUser();
        $this->validate();
        if ($this->menuId) {
            $parents = Menu::pluck('parent_id', 'id')->all();
            $parents[$this->menuId] = $this->parent_id ?: null;
            $this->validateParents($parents);
        }
        if ($this->route && $this->route !== '#' && ! Route::has($this->route)) {
            throw ValidationException::withMessages(['route' => 'Seleccione uma rota existente.']);
        }

        if ($this->menuId) {
            $menu = Menu::findOrFail($this->menuId);
        } else {
            $menu = new Menu;
        }

        $menu->parent_id = $this->parent_id;
        $menu->module_id = $this->module_id;
        $menu->menu_name = $this->menu_name;
        $menu->slug = $this->slug ?: \Str::slug($this->menu_name);
        $menu->order_priority = $this->order_priority ?? 0;
        $menu->route = $this->route;
        $menu->icon = $this->icon;
        $menu->permission = $this->permission;
        $menu->description = $this->description;
        $menu->save();

        // limpar cache
        Menu::clearMenuCacheForUser(auth()->id());

        $this->showModal = false;
        $this->loadMenus();
        $this->dispatch('toast', type: 'success', message: 'Menu salvo.');
    }

    public function delete($id)
    {
        $this->authorizeUser();
        $menu = Menu::findOrFail($id);
        $menu->delete();
        Menu::clearMenuCacheForUser(auth()->id());
        $this->loadMenus();
        $this->dispatch('toast', type: 'success', message: 'Menu eliminado.');
    }

    /**
     * Save order submitted from SortableJS
     * Expecting array of nodes: [
     *  {id:1, parent_id:null, order:0},
     *  {id:2, parent_id:1, order:0},
     *  ...
     * ]
     */
    public function saveOrder($nodes)
    {
        $this->authorizeUser();
        $validated = Validator::make(['nodes' => $nodes], [
            'nodes' => ['required', 'array', 'min:1'],
            'nodes.*.id' => ['required', 'integer', 'distinct', 'exists:menus,id'],
            'nodes.*.parent_id' => ['present', 'nullable', 'integer', 'exists:menus,id'],
            'nodes.*.order' => ['required', 'integer', 'min:0'],
        ])->validate();
        DB::transaction(function () use ($validated) {
            $parents = Menu::lockForUpdate()->pluck('parent_id', 'id')->all();
            foreach ($validated['nodes'] as $node) {
                $parents[$node['id']] = $node['parent_id'];
            }
            $this->validateParents($parents);
            foreach ($validated['nodes'] as $node) {
                Menu::whereKey($node['id'])->update(['parent_id' => $node['parent_id'], 'order_priority' => $node['order']]);
            }
        });

        Menu::clearMenuCacheForUser(auth()->id());
        $this->loadMenus();
        $this->dispatch('toast', type: 'success', message: 'Ordem atualizada.');
    }

    private function validateParents(array $parents): void
    {
        foreach (array_keys($parents) as $id) {
            $seen = [];
            while ($id !== null && isset($parents[$id])) {
                if (isset($seen[$id])) {
                    throw ValidationException::withMessages(['parent_id' => 'Um menu não pode ser descendente de si próprio.']);
                }
                $seen[$id] = true;
                $id = $parents[$id];
            }
        }
    }

    public function render()
    {
        return view('livewire.menu-builder');
    }
}
