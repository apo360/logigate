<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $modulo = null)
    {
        $empresa = Auth::user()->empresas->first();
        
        if (!$empresa) {
            abort(403, 'Nenhuma empresa associada');
        }

        // Se não tem subscrição ativa
        if (!$empresa->temSubscricaoAtiva()) {
            // Permitir acesso a rota de subscrição
            if ($request->routeIs('subscricao.*')) {
                return $next($request);
            }
            
            // Redirecionar para página de subscrição
            return redirect()->route('subscricao.nova')
                           ->with('warning', 'É necessário uma subscrição ativa para acessar esta funcionalidade');
        }

        // Verificar módulo específico
        if ($modulo) {
            $moduloAtivo = $empresa->modulosAtivos()
                                 ->where('codigo', $modulo)
                                 ->first();
            
            if (!$moduloAtivo) {
                abort(403, 'Módulo não disponível na sua subscrição');
            }
        }

        return $next($request);
    }
}
