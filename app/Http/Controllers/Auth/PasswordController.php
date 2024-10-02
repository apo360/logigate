<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function showChangeForm()
    {
        return view('auth.change-password'); 
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->password_changed = true; // Atualiza o campo para verdadeiro
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Senha alterada com sucesso!');
    }
}
