<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClientePortalEnsureActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('cliente_portal');
        $portalUser = $guard->user();

        if (! $portalUser) {
            return redirect()->route('cliente.portal.login');
        }

        if (isset($portalUser->is_active) && ! $portalUser->is_active) {
            return $this->logout($request, 'A sua conta do portal do cliente está inativa.');
        }

        $customer = $portalUser->customer;

        if (! $customer) {
            return $this->logout($request, 'A sua conta do portal do cliente não está vinculada a um cliente.');
        }

        // The credential authorizes one Customer/Empresa pair, not all customer memberships.
        if (! $portalUser->empresa_id || ! $this->customerCanUseEmpresa($customer, (int) $portalUser->empresa_id)) {
            return $this->logout($request, 'Empresa do portal não autorizada.');
        }
        $request->session()->put('cliente_portal_empresa_id', (int) $portalUser->empresa_id);

        return $next($request);
    }

    private function logout(Request $request, string $message): RedirectResponse
    {
        Auth::guard('cliente_portal')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('cliente.portal.login')
            ->with('error', $message);
    }

    private function customerCanUseEmpresa($customer, int $empresaId): bool
    {
        return (int) $customer->empresa_id === $empresaId
            || $customer->empresas()->where('empresas.id', $empresaId)->exists();
    }
}
