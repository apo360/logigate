<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMasterMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o administrador master está autenticado via sessão
        if (!session()->has('admin_logged_in') || session('admin_logged_in') !== true) {
            // Caso não esteja autenticado, redireciona para a página de login/pin
            return redirect()->route('verify-pin')->with('error', 'Acesso restrito. Insira o PIN de administrador.');
        }

        return $next($request);
    }
}
