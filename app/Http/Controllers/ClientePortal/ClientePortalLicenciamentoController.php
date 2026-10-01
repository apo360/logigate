<?php

namespace App\Http\Controllers\ClientePortal;

use App\Http\Controllers\Controller;
use App\Models\MercadoriaAgrupada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

class ClientePortalLicenciamentoController extends Controller
{
    public function index(): RedirectResponse|View
    {
        $portal = Auth::guard('cliente_portal')->user();
        $customer = $portal->customer;
        $licenciamentos = $customer->licenciamento()->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->where('empresa_id', $portal->empresa_id)->latest('id')->paginate(15);

        if (! ViewFactory::exists('WebSite.ClienteAppPage.licenciamentos')) {
            return redirect()
                ->route('cliente.portal.dashboard')
                ->with('status', 'A listagem de licenciamentos do Portal Cliente ainda não possui uma view dedicada.');
        }

        return view('WebSite.ClienteAppPage.licenciamentos', compact('customer', 'licenciamentos'));
    }

    public function show(int $licenciamentoId): RedirectResponse|View
    {
        $portal = Auth::guard('cliente_portal')->user();
        $customer = $portal->customer;
        $licenciamento = $customer->licenciamento()->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->where('empresa_id', $portal->empresa_id)
            ->with(['cliente' => fn ($query) => $query->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->whereKey($portal->customer_id), 'empresa'])
            ->whereKey($licenciamentoId)
            ->firstOrFail();

        $mercadoriaAgrupadas = MercadoriaAgrupada::query()
            ->with('mercadorias')
            ->where('licenciamento_id', $licenciamento->id)
            ->get();

        return view('WebSite.ClienteAppPage.rastreamento_resultado', compact('licenciamento', 'mercadoriaAgrupadas'));
    }

    public function rastreamento(): View
    {
        return view('WebSite.ClienteAppPage.rastreamento');
    }

    public function result(Request $request): RedirectResponse|View
    {
        $data = $request->validate([
            'codigo_licenciamento' => ['required', 'string', 'max:255'],
        ]);

        $portal = Auth::guard('cliente_portal')->user();
        $customer = $portal->customer;
        $licenciamento = $customer->licenciamento()->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->where('empresa_id', $portal->empresa_id)
            ->with(['cliente' => fn ($query) => $query->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->whereKey($portal->customer_id), 'empresa'])
            ->where('codigo_licenciamento', $data['codigo_licenciamento'])
            ->first();

        if (! $licenciamento) {
            return back()->with('error', 'Licenciamento não encontrado para este cliente.');
        }

        $mercadoriaAgrupadas = MercadoriaAgrupada::query()
            ->with('mercadorias')
            ->where('licenciamento_id', $licenciamento->id)
            ->get();

        return view('WebSite.ClienteAppPage.rastreamento_resultado', compact('licenciamento', 'mercadoriaAgrupadas'));
    }
}
