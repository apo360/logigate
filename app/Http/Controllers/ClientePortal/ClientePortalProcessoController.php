<?php

namespace App\Http\Controllers\ClientePortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

class ClientePortalProcessoController extends Controller
{
    public function index(): RedirectResponse|View
    {
        $portal = Auth::guard('cliente_portal')->user();
        $customer = $portal->customer;
        $processos = $customer->processos()->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->where('empresa_id', $portal->empresa_id)->latest('id')->paginate(15);

        if (! ViewFactory::exists('WebSite.ClienteAppPage.processos')) {
            return redirect()
                ->route('cliente.portal.dashboard')
                ->with('status', 'A listagem de processos do Portal Cliente ainda não possui uma view dedicada.');
        }

        return view('WebSite.ClienteAppPage.processos', compact('customer', 'processos'));
    }

    public function show(int $processoId): RedirectResponse|View
    {
        $portal = Auth::guard('cliente_portal')->user();
        $customer = $portal->customer;
        $processo = $customer->processos()->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->where('empresa_id', $portal->empresa_id)
            ->whereKey($processoId)
            ->firstOrFail();

        if (! ViewFactory::exists('WebSite.ClienteAppPage.processo_show')) {
            return redirect()
                ->route('cliente.portal.dashboard')
                ->with('status', 'O detalhe de processos do Portal Cliente ainda não possui uma view dedicada.');
        }

        return view('WebSite.ClienteAppPage.processo_show', compact('customer', 'processo'));
    }
}
