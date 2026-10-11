<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\EffectiveModuleResolver;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class EnsureModuleEntitlement
{
    public function handle(Request $request, Closure $next)
    {
        // Scope is the reviewed destination map, never every authenticated route.
        if (! $request->user() instanceof User) {
            return $next($request);
        }
        $route = $request->route()?->getName();
        foreach (config('navigation.route_modules', []) as $pattern => $code) {
            if ($route && Str::is($pattern, $route)) {
                $empresa = TenantContext::empresaId();
                abort_unless($empresa, 403, 'Seleccione a empresa activa.');
                $resolver = app(EffectiveModuleResolver::class);
                $moduleId = $resolver->moduleId($code);
                abort_unless($moduleId && in_array($moduleId, $resolver->ids($empresa), true), 403, 'Módulo não disponível para esta empresa.');
                break;
            }
        }

        return $next($request);
    }
}
