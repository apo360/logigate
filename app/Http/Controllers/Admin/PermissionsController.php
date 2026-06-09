<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Usuarios\Actions\AtualizarPermissaoAction;
use App\Domains\Usuarios\Actions\CriarPermissaoAction;
use App\Domains\Usuarios\Actions\ExcluirPermissaoAction;
use App\Domains\Usuarios\Queries\ListarPermissoesQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    public function index(ListarPermissoesQuery $query)
    {
        $permissions = $query->execute();

        return view('admin.permissions', compact('permissions'));
    }

    public function create()
    {
        return view('admin.create_permission');
    }

    public function store(Request $request, CriarPermissaoAction $action)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:permissions,name'],
        ]);

        $action->execute(Auth::user(), $validated['name']);

        return redirect()->route('permissions.index')->with('success', 'Permissão criada com sucesso!');
    }

    public function show(string $id)
    {
        return redirect()->route('permissions.index');
    }

    public function edit(Permission $permission)
    {
        return view('admin.edit_permission', compact('permission'));
    }

    public function update(Request $request, Permission $permission, AtualizarPermissaoAction $action)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:permissions,name,' . $permission->id],
        ]);

        $action->execute(Auth::user(), $permission, $validated['name']);

        return redirect()->route('permissions.index')->with('success', 'Permissão atualizada com sucesso!');
    }

    public function destroy(Permission $permission, ExcluirPermissaoAction $action)
    {
        $action->execute(Auth::user(), $permission);

        return redirect()->route('permissions.index')->with('success', 'Permissão excluída com sucesso!');
    }
}
