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
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            abort(403, 'Nenhuma empresa associada');
        }

        // Resolve the subscription explicitly so dashboard access does not depend on
        // brittle helper macros or legacy uppercase status values.
        $activeSubscription = $empresa->subscricoes()
            ->active()
            ->where(function ($query) {
                $query->whereNull('data_expiracao')
                    ->orWhere('data_expiracao', '>', now());
            })
            ->latest('id')
            ->first();

        if (! $activeSubscription) {
            $pendingSubscription = $empresa->subscricoes()
                ->pending()
                ->latest('id')
                ->first();

            if ($pendingSubscription) {
                return redirect()
                    ->route('checkout', ['conta' => $empresa->conta])
                    ->with('warning', 'Complete o pagamento para ativar a sua subscrição.');
            }

            return redirect()
                ->route('subscribe.view', ['empresa' => $empresa->id])
                ->with('warning', 'É necessária uma subscrição ativa para acessar esta funcionalidade.');
        }

        // Verificar módulo específico
        if ($modulo) {
            $moduloAtivo = $activeSubscription->activatedModules()
                ->with('module')
                ->where('active', true)
                ->get()
                ->pluck('module')
                ->firstWhere('codigo', $modulo);

            if (! $moduloAtivo) {
                abort(403, 'Módulo não disponível na sua subscrição');
            }
        }

        return $next($request);
    }
}
