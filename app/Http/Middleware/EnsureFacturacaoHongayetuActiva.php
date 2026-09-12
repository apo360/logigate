<?php

namespace App\Http\Middleware;

use App\Application\Integracoes\Services\IntegracaoResolverService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFacturacaoHongayetuActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $empresaId = $request->user()?->empresaAtiva()?->id;

        if (! app(IntegracaoResolverService::class)->isFacturacaoHongayetuActiva($empresaId)) {
            return redirect()
                ->route('integracoes.facturacao-hongayetu')
                ->with('status', 'A integração de Facturação Hongayetu precisa estar activa para aceder a esta área.');
        }

        return $next($request);
    }
}
