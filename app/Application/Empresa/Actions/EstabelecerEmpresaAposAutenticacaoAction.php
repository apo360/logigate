<?php

namespace App\Application\Empresa\Actions;

use App\Models\Empresa;
use App\Models\User;
use App\Support\TenantContext;

final class EstabelecerEmpresaAposAutenticacaoAction
{
    public function execute(User $user): ?Empresa
    {
        $authenticated = \Illuminate\Support\Facades\Auth::user();
        if (! ($authenticated instanceof User) || ! $authenticated->is($user)) {
            return null;
        }

        if ($empresa = TenantContext::empresa($user)) {
            return $empresa;
        }

        TenantContext::clear();
        $empresas = $user->empresas()->limit(2)->get();
        if ($empresas->count() !== 1) {
            return null;
        }

        $empresa = $empresas->first();
        TenantContext::setEmpresa($user, $empresa);

        return $empresa;
    }
}
