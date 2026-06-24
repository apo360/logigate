<?php

namespace App\Http\Controllers\ClientePortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientePortalEmpresaContextController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'empresa_id' => ['required', 'integer'],
        ]);

        $customer = Auth::guard('cliente_portal')->user()->customer;
        $empresaId = (int) $data['empresa_id'];

        abort_unless(
            (int) $customer->empresa_id === $empresaId
                || $customer->empresas()->where('empresas.id', $empresaId)->exists(),
            403
        );

        $request->session()->put('cliente_portal_empresa_id', $empresaId);

        return back()->with('status', 'Empresa de contexto atualizada.');
    }
}
