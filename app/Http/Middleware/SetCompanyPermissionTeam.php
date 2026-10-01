<?php

namespace App\Http\Middleware;

use App\Support\CompanyRbac;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;

class SetCompanyPermissionTeam
{
    public function handle(Request $request, Closure $next): mixed
    {
        CompanyRbac::activate();
        $id = TenantContext::empresaId();
        if ($id && $request->exists('empresa_id')) {
            abort_unless((string) $request->input('empresa_id') === (string) $id
                || $request->routeIs('empresa-contexto.*', 'empresa-context.*'), 403);
        }
        return $next($request);
    }
}
