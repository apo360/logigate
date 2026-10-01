<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Usuarios\Actions\AtualizarRoleAction;
use App\Domains\Usuarios\Actions\CriarRoleAction;
use App\Domains\Usuarios\Actions\ExcluirRoleAction;
use App\Domains\Usuarios\Queries\ListarPermissoesQuery;
use App\Domains\Usuarios\Queries\ListarRolesQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class Roles extends Controller
{
    public function index(ListarRolesQuery $query)
    {
        $empresa = \App\Support\TenantContext::empresa();
        abort_unless($empresa && auth()->user()->hasRole('Administrador'), 403);
        $roles = $query->execute();

        return view('admin.roles', compact('roles'));
    }

    public function create(ListarPermissoesQuery $query)
    {
        $empresa = \App\Support\TenantContext::empresa();
        abort_unless($empresa && auth()->user()->hasRole('Administrador'), 403);
        $permissions = $query->execute();

        return view('admin.create_role', compact('permissions'));
    }

    public function store(Request $request, CriarRoleAction $action)
    {
        $empresa = \App\Support\TenantContext::empresa();
        abort_unless($empresa && auth()->user()->hasRole('Administrador'), 403);
        $validated = $request->validate([
            'name' => ['required', 'string', Rule::unique('roles', 'name')->where('empresa_id', \App\Support\TenantContext::empresaId())],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $action->execute(Auth::user(), $validated['name'], $validated['permissions']);

        return redirect()->route('roles.index')->with('success', 'Papel criado com sucesso!');
    }

    public function show(string $id)
    {
        $empresa = \App\Support\TenantContext::empresa();
        abort_unless($empresa && auth()->user()->hasRole('Administrador'), 403);
        return redirect()->route('roles.index');
    }

    public function edit(Role $role, ListarPermissoesQuery $query)
    {
        $empresa = \App\Support\TenantContext::empresa();
        abort_unless($empresa && auth()->user()->hasRole('Administrador'), 403);
        abort_unless((int) $role->empresa_id === (int) $empresa->id, 403);
        $permissions = $query->execute();

        return view('admin.edit_role', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role, AtualizarRoleAction $action)
    {
        $empresa = \App\Support\TenantContext::empresa();
        abort_unless($empresa && auth()->user()->hasRole('Administrador'), 403);
        abort_unless((int) $role->empresa_id === (int) $empresa->id, 403);
        $validated = $request->validate([
            'name' => ['required', 'string', Rule::unique('roles', 'name')->where('empresa_id', \App\Support\TenantContext::empresaId())->ignore($role->id)],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $action->execute(Auth::user(), $role, $validated['name'], $validated['permissions']);

        return redirect()->route('roles.index')->with('success', 'Papel atualizado com sucesso!');
    }

    public function destroy(Role $role, ExcluirRoleAction $action)
    {
        $empresa = \App\Support\TenantContext::empresa();
        abort_unless($empresa && auth()->user()->hasRole('Administrador'), 403);
        $action->execute(Auth::user(), $role);

        return redirect()->route('roles.index')->with('success', 'Papel excluído com sucesso!');
    }
}
