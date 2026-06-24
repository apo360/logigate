<?php

namespace App\Http\Controllers\ClientePortal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

class ClientePortalDashboardController extends Controller
{
    public function __invoke(): View
    {
        $portalUser = Auth::guard('cliente_portal')->user();
        $customer = $portalUser->customer;
        $empresasAssociadas = $customer->empresas()->get();

        return view($this->dashboardView(), [
            'portalUser' => $portalUser,
            'portal' => $portalUser,
            'customer' => $customer,
            'processosCount' => $customer->processos()->count(),
            'licenciamentosCount' => $customer->licenciamento()->count(),
            'documentosCount' => $customer->documentosArquivos()->count(),
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
