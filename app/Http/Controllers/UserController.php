<?php

namespace App\Http\Controllers;

use App\Models\EmpresaUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obter a empresa do usuário autenticado
        $empresaId = Auth::user()->empresas->first()->id;

        // Buscar os usuários que pertencem à empresa específica
        $users = User::whereHas('empresas', function ($query) use ($empresaId) {
            $query->where('empresa_id', $empresaId);
        })->with('roles')->get();

        return view('usuario.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all(); // Obter todos os papéis disponíveis
        return view('usuario.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required' // Papel é obrigatório
        ]);

        // Criar o usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        EmpresaUser::create([
            'empresa_id' => Auth::user()->empresas->first()->id,
            'user_id' => $user->id
        ]);

        // Atribuir o papel
        $user->assignRole($request->role);

        return redirect()->route('usuarios.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Formulário para adicionar permissões ao usuário
    public function editPermissions(User $user)
    {
        $permissions = Permission::all(); // Obter todas as permissões
        return view('usuario.permissions', compact('user', 'permissions'));
    }

    // Armazenar as permissões do usuário
    public function storePermissions(Request $request, User $user)
    {
        $user->syncPermissions($request->permissions); // Sincronizar permissões
        return redirect()->route('usuarios.index')->with('success', 'Permissões atribuídas com sucesso!');
    }
}
