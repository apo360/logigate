<?php

namespace App\Http\Controllers\ClientePortal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DocumentoArquivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

class ClientePortalDashboardController extends Controller
{
    public function __invoke(): View
    {
        $portalUser = Auth::guard('cliente_portal')->user();
        $customer = $portalUser->customer;
        $empresasAssociadas = $customer->empresas()->get();
        $documentosCount = DocumentoArquivo::query()
            ->where('empresa_id', $portalUser->empresa_id)
            ->where('customer_id', $portalUser->customer_id)
            ->where('contexto', 'customer')
            ->where('documentable_type', Customer::class)
            ->where('documentable_id', $portalUser->customer_id)
            ->latest('id')
            ->limit(250)
            ->get()
            ->filter(fn (DocumentoArquivo $documento): bool => Gate::forUser($portalUser)->allows('viewPortal', $documento))
            ->count();

        return view($this->dashboardView(), [
            'portalUser' => $portalUser,
            'portal' => $portalUser,
            'customer' => $customer,
            'processosCount' => $customer->processos()->count(),
            'licenciamentosCount' => $customer->licenciamento()->count(),
            'documentosCount' => $documentosCount,
            'currentEmpresaId' => session('cliente_portal_empresa_id'),
            'empresasAssociadas' => $empresasAssociadas,
        ]);
    }

    private function dashboardView(): string
    {
        return ViewFactory::exists('WebSite.ClienteAppPage.dashboard')
            ? 'WebSite.ClienteAppPage.dashboard'
            : 'WebSite.ClienteAppPage.index';
    }
}
