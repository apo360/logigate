<?php

namespace App\Http\Controllers;

use App\Domains\Billing\Enums\BillingCycle;
use App\Services\SubscriptionMaintenance;
use App\Support\TenantContext;
use Illuminate\Http\Request;

class SubscricaoController extends AuthenticatedController
{
    public function index()
    {
        return redirect()->route('billing.plans');
    }

    public function store(Request $request)
    {
        $empresaId = TenantContext::empresaId();
        abort_unless($empresaId && (! $request->filled('empresa_id') || (int) $request->empresa_id === $empresaId), 403);
        $request->merge(['modalidade_pagamento' => BillingCycle::fromInput(strtolower($request->input('modalidade_pagamento', 'mensal')))->value]);

        return app(BillingPlanController::class)->start($request);
    }

    public function checkExpiradas()
    {
        return response()->json(['count' => app(SubscriptionMaintenance::class)->expire()]);
    }
}
