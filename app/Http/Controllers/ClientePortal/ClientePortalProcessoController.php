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
        $customer = Auth::guard('cliente_portal')->user()->customer;
        $processos = $customer->processos()->latest('id')->paginate(15);

        if (! ViewFactory::exists('WebSite.ClienteAppPage.processos')) {
            return redirect()
                ->route('cliente.portal.dashboard')
                ->with('status', 'A listagem de processos do Portal Cliente ainda não possui uma view dedicada.');
        }

        return view('WebSite.ClienteAppPage.processos', compact('customer', 'processos'));
    }

    public function show(int $processoId): RedirectResponse|View
    {
        $customer = Auth::guard('cliente_portal')->user()->customer;
        $processo = $customer->processos()
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
