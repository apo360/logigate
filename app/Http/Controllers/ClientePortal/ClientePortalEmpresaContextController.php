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

        $portal = Auth::guard('cliente_portal')->user();
        $customer = $portal->customer;
        $empresaId = (int) $data['empresa_id'];

        abort_unless(
            (int) $portal->empresa_id === $empresaId && $customer !== null,
            403
        );

        $request->session()->put('cliente_portal_empresa_id', $empresaId);

        return back()->with('status', 'Empresa de contexto atualizada.');
    }
}
