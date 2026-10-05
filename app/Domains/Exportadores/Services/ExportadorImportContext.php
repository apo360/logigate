<?php

namespace App\Domains\Exportadores\Services;

use App\Models\Empresa;
use App\Models\User;
use App\Support\CompanyRbac;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class ExportadorImportContext
{
    public function run(User $actor, Empresa $empresa, callable $callback): mixed
    {
        abort_unless(TenantContext::userBelongsToEmpresa($actor, $empresa->id), 403);
        $guard = Auth::guard('web');
        $previousUser = $guard->user();
        $previousEmpresa = session('empresa_id');
        $guard->setUser($actor);
        session()->put('empresa_id', (int) $empresa->id);
        try {
            return CompanyRbac::within($empresa->id, function () use ($actor, $callback) {
                CompanyRbac::forgetUser($actor);
                Gate::forUser($actor)->authorize('create', \App\Models\Exportador::class);
                return $callback();
            });
        } finally {
            $previousUser ? $guard->setUser($previousUser) : $guard->forgetUser();
            $previousEmpresa === null ? session()->forget('empresa_id') : session()->put('empresa_id', $previousEmpresa);
            CompanyRbac::activate();
        }
    }
}
