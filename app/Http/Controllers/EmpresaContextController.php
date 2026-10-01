<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Support\TenantContext;
use Illuminate\Http\Request;

class EmpresaContextController extends BaseController
{
    public function index(Request $request)
    {
        return view('empresa.selecionar', [
            'empresas' => $request->user()->empresas()->get(),
            'activeEmpresaId' => TenantContext::empresaId(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['empresa_id' => ['required', 'integer', 'min:1']]);
        // Membership lookup is deliberately independent of the active tenant.
        $empresa = $request->user()->empresas()->where('empresas.id', $data['empresa_id'])->first();
        abort_unless($empresa instanceof Empresa, 403);
        TenantContext::setEmpresa($request->user(), $empresa);

        return redirect()->route('dashboard');
    }
}
