<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('manageGlobalPermissions');
        $permissions = Permission::all();
        return view('admin.permissions', compact('permissions'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('manageGlobalPermissions');
        return view('admin.create_permission');
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('manageGlobalPermissions');
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        Permission::create(['name' => $request->name]);

        return redirect()->route('permissions.index')->with('success', 'Permissão criada com sucesso!');
    }

    public function edit(Permission $permission)
    {
        \Illuminate\Support\Facades\Gate::authorize('manageGlobalPermissions');
        return view('admin.edit_permission', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        \Illuminate\Support\Facades\Gate::authorize('manageGlobalPermissions');
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id
        ]);

        $permission->update(['name' => $request->name]);

        return redirect()->route('permissions.index')->with('success', 'Permissão atualizada com sucesso!');
    }

    public function destroy(Permission $permission)
    {
        \Illuminate\Support\Facades\Gate::authorize('manageGlobalPermissions');
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permissão excluída com sucesso!');
    }
}

