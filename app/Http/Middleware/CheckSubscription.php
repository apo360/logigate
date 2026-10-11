<?php

namespace App\Http\Middleware;

use App\Services\EffectiveModuleResolver;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $modulo = null)
    {
        $empresa = TenantContext::empresa();

        if (! $empresa) {
            abort(403, 'Nenhuma empresa associada');
        }

        // Resolve the subscription explicitly so dashboard access does not depend on
        // brittle helper macros or legacy uppercase status values.
        $resolver = app(EffectiveModuleResolver::class);
        $hasSubscription = $resolver->hasSubscription((int) $empresa->id);

        if (! $hasSubscription) {
            $pendingSubscription = $empresa->subscricoes()
                ->pending()
                ->latest('id')
                ->first();

            if ($pendingSubscription) {
                return redirect()
                    ->route('billing.checkout')
                    ->with('warning', 'Complete o pagamento para ativar a sua subscrição.');
            }

            return redirect()
                ->route('billing.plans')
                ->with('warning', 'Escolha um plano para activar o acesso à plataforma.');
        }

        // Verificar módulo específico
        if ($modulo) {
            $moduleId = $resolver->moduleId((string) $modulo);
            if (! $moduleId || ! in_array($moduleId, $resolver->ids((int) $empresa->id), true)) {
                abort(403, 'Módulo não disponível na sua subscrição');
            }
        }

        return $next($request);
    }
}
