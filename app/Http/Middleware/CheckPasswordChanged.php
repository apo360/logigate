<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Verifique se o usuário está autenticado
        if ($user) {
            // Verifica se o usuário não é admin e se ainda não trocou a senha
            if (!$user->roles->contains('name', 'Adminstrador') && !$user->password_changed) {
                // Redireciona o usuário para a página de alteração de senha
                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
