<?php

namespace App\Http\Controllers;

use App\Domains\Empresa\Queries\ObterEmpresaAtualQuery;
use App\Domains\Usuarios\Actions\AtualizarUsuarioEmpresaAction;
use App\Domains\Usuarios\Actions\BloquearUsuarioAction;
use App\Domains\Usuarios\Actions\CriarUsuarioEmpresaAction;
use App\Domains\Usuarios\Actions\DesbloquearUsuarioAction;
use App\Domains\Usuarios\Actions\RemoverUsuarioDaEmpresaAction;
use App\Domains\Usuarios\Actions\ResetarSenhaUsuarioAction;
use App\Domains\Usuarios\Actions\SincronizarPermissoesUsuarioAction;
use App\Domains\Usuarios\Data\UsuarioEmpresaData;
use App\Domains\Usuarios\Queries\ListarPermissoesQuery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends AuthenticatedController
{
    public function index(ObterEmpresaAtualQuery $empresaAtual)
    {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);
        Gate::forUser(Auth::user())->authorize('manageUsers', $empresa);

        return view('usuario.index', compact('empresa'));
    }

    public function create(ObterEmpresaAtualQuery $empresaAtual)
    {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);
        Gate::forUser(Auth::user())->authorize('manageUsers', $empresa);

        return view('usuario.create', compact('empresa'));
    }

    public function store(
        Request $request,
        ObterEmpresaAtualQuery $empresaAtual,
        CriarUsuarioEmpresaAction $action
    ) {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);
        Gate::forUser(Auth::user())->authorize('manageUsers', $empresa);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $action->execute(Auth::user(), $empresa, UsuarioEmpresaData::fromArray($validated));

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function show(string $id, ObterEmpresaAtualQuery $empresaAtual)
    {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);

        $user = User::with('roles', 'permissions', 'audits')->findOrFail($id);
        Gate::forUser(Auth::user())->authorize('manageUser', [$empresa, $user]);

        return view('usuario.show', compact('empresa', 'user'));
    }

    public function edit(string $id)
    {
        return redirect()->route('usuarios.show', $id);
    }

    public function update(
        Request $request,
        string $id,
        ObterEmpresaAtualQuery $empresaAtual,
        AtualizarUsuarioEmpresaAction $action
    ) {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);

        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $action->execute(Auth::user(), $empresa, $user, UsuarioEmpresaData::fromArray($validated));

        return redirect()->route('usuarios.show', $user)->with('success', 'Usuário actualizado com sucesso!');
    }

    public function destroy(string $id, ObterEmpresaAtualQuery $empresaAtual, RemoverUsuarioDaEmpresaAction $action)
    {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);

        $action->execute(Auth::user(), $empresa, User::findOrFail($id));

        return redirect()->route('usuarios.index')->with('success', 'Usuário removido da empresa com sucesso!');
    }

    public function editPermissions(
        User $user,
        ObterEmpresaAtualQuery $empresaAtual,
        ListarPermissoesQuery $permissionsQuery
    ) {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);
        Gate::forUser(Auth::user())->authorize('manageUser', [$empresa, $user]);

        $permissions = $permissionsQuery->execute();

        return view('usuario.permissions', compact('empresa', 'user', 'permissions'));
    }

    public function storePermissions(
        Request $request,
        User $user,
        ObterEmpresaAtualQuery $empresaAtual,
        SincronizarPermissoesUsuarioAction $action
    ) {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $action->execute(Auth::user(), $empresa, $user, $validated['permissions'] ?? []);

        return redirect()->route('usuarios.index')->with('success', 'Permissões atribuídas com sucesso!');
    }

    public function block($id, ObterEmpresaAtualQuery $empresaAtual, BloquearUsuarioAction $action)
    {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);

        $action->execute(Auth::user(), $empresa, User::findOrFail($id));

        return redirect()->route('usuarios.index')->with('success', 'Usuário bloqueado com sucesso!');
    }

    public function unblock($id, ObterEmpresaAtualQuery $empresaAtual, DesbloquearUsuarioAction $action)
    {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);

        $action->execute(Auth::user(), $empresa, User::findOrFail($id));

        return redirect()->route('usuarios.index')->with('success', 'Usuário desbloqueado com sucesso!');
    }

    public function resert_pass($id, ObterEmpresaAtualQuery $empresaAtual, ResetarSenhaUsuarioAction $action)
    {
        $empresa = $empresaAtual->execute(Auth::user());
        abort_unless($empresa, 403);

        $temporaryPassword = $action->execute(Auth::user(), $empresa, User::findOrFail($id));

        return redirect()->route('usuarios.index')
            ->with('success', 'Senha temporária gerada com sucesso.')
            ->with('temporary_password', $temporaryPassword);
    }
}
