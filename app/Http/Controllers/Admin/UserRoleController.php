<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Empresa\Queries\ObterEmpresaAtualQuery;
use App\Domains\Usuarios\Actions\SincronizarRolesUsuarioAction;
use App\Domains\Usuarios\Queries\ListarRolesQuery;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRoleController extends Controller
{
    public function assignRole(
        Request $request,
        User $user,
        ObterEmpresaAtualQuery $empresaAtual,
        SincronizarRolesUsuarioAction $action
    ) {
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);

        $roles = $user->roles
            ->pluck('name')
            ->push($validated['role'])
            ->unique()
            ->values()
            ->all();

        $action->execute(Auth::user(), $empresa, $user, $roles);

        return redirect()->back()->with('success', 'Papel atribuído com sucesso!');
    }

    public function removeRole(
        Request $request,
        User $user,
        ObterEmpresaAtualQuery $empresaAtual,
        SincronizarRolesUsuarioAction $action
    ) {
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);

        $roles = $user->roles
            ->pluck('name')
            ->reject(fn (string $role) => $role === $validated['role'])
            ->values()
            ->all();

        $action->execute(Auth::user(), $empresa, $user, $roles);

        return redirect()->back()->with('success', 'Papel removido com sucesso!');
    }

    public function showAssignRoleForm(User $user, ListarRolesQuery $query)
    {
        $roles = $query->execute();

        return view('admin.assign_role', compact('user', 'roles'));
    }
}
