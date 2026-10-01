<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;

class EnsureActiveEmpresa
{
    public function handle(Request $request, Closure $next)
    {
        if (TenantContext::empresaId() === null) {
            if ($request->expectsJson()) {
                abort(403, 'Selecione uma empresa válida.');
            }

            return redirect()->route('empresa-context.index');
        }

        return $next($request);
    }
}
