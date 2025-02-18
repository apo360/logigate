<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        // Obter a designação da primeira empresa associada ao usuário
        $designacao = $user->empresas->first()->Designacao;

        // Redirecionamento com base na designação
        switch ($designacao) {
            case 'Despachante Oficial':
                return redirect()->route('dashboard'); // Certifique-se de que a rota existe
            case 'Transitário':
                return redirect()->route('transitario.dashboard'); // Certifique-se de que a rota existe
            case 'Agente de Carga':
                return redirect()->route('agente_carga.dashboard'); // Certifique-se de que a rota existe
            default:
                Log::warning('Unknown designation', ['designacao' => $designacao]);
                return redirect('/dashboard'); // Redirecionamento padrão
        }
    }
}
