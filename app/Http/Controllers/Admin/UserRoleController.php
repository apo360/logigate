<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $role = Role::findByName($request->input('role'));
        $user->assignRole($role);

        return redirect()->back()->with('success', 'Papel atribuído com sucesso!');
    }

    public function removeRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $role = Role::findByName($request->input('role'));
        $user->removeRole($role);

        return redirect()->back()->with('success', 'Papel removido com sucesso!');
    }

    public function showAssignRoleForm(User $user)
    {
        $roles = Role::all();
        return view('admin.assign_role', compact('user', 'roles'));
    }
}
