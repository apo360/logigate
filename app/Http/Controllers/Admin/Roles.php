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

class Roles extends Controller
{
    public function index(ListarRolesQuery $query)
    {
        $roles = $query->execute();

        return view('admin.roles', compact('roles'));
    }

    public function create(ListarPermissoesQuery $query)
    {
        $permissions = $query->execute();

        return view('admin.create_role', compact('permissions'));
    }

    public function store(Request $request, CriarRoleAction $action)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $action->execute(Auth::user(), $validated['name'], $validated['permissions']);

        return redirect()->route('roles.index')->with('success', 'Papel criado com sucesso!');
    }

    public function show(string $id)
    {
        return redirect()->route('roles.index');
    }

    public function edit(Role $role, ListarPermissoesQuery $query)
    {
        $permissions = $query->execute();

        return view('admin.edit_role', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role, AtualizarRoleAction $action)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name,' . $role->id],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $action->execute(Auth::user(), $role, $validated['name'], $validated['permissions']);

        return redirect()->route('roles.index')->with('success', 'Papel atualizado com sucesso!');
    }

    public function destroy(Role $role, ExcluirRoleAction $action)
    {
        $action->execute(Auth::user(), $role);

        return redirect()->route('roles.index')->with('success', 'Papel excluído com sucesso!');
    }
}
