<?php

namespace App\Http\Controllers;

use App\Domains\Billing\Enums\BillingCycle;
use App\Models\Empresa;
use App\Models\PagamentoOnline;
use App\Models\Plano;
use App\Models\Subscricao;
use App\Services\BillingAccess;
use App\Services\StartSubscription;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BillingPlanController extends Controller
{
    public function index(): View
    {
        $empresa = TenantContext::empresa();
        abort_unless($empresa, 403, 'Nenhuma empresa associada.');
        $activeSubscription = $empresa->subscricoes()->with('plano')->effective()->latest('id')->first();
        $pendingSubscription = $empresa->subscricoes()->with('plano')->pending()->latest('id')->first();
        $futureSubscription = $empresa->subscricoes()->with('plano')->active()->where('data_inicio', '>', now())->latest('id')->first();
        $planos = Plano::where('status', 'activo')->with(['modulos', 'itemplano'])->orderBy('ordem')->orderBy('preco_mensal')->get();
        $payments = PagamentoOnline::where('empresa_id', $empresa->id)->latest('id')->paginate(15, ['*'], 'payments_page');
        $canManage = app(BillingAccess::class)->canManage(auth()->user(), $empresa->id);
        $notices = auth()->user()->notifications()->where('data->empresa_id', $empresa->id)->where('data->type', 'subscription_renewal')->latest()->limit(5)->get();

        return view('billing.plans', compact('empresa', 'activeSubscription', 'pendingSubscription', 'futureSubscription', 'planos', 'payments', 'canManage', 'notices'));
    }

    public function start(Request $request): RedirectResponse
    {
        $empresaId = TenantContext::empresaId();
        abort_unless($empresaId, 403);
        app(BillingAccess::class)->authorize($empresaId);
        $data = $request->validate(['plano_id' => ['required', 'integer', 'exists:planos,id'], 'modalidade_pagamento' => ['required', 'in:monthly,trimestral,semestral,annual'], 'renew' => ['sometimes', 'boolean']]);
        $sub = app(StartSubscription::class)->execute($empresaId, (int) $data['plano_id'], BillingCycle::fromInput($data['modalidade_pagamento']), auth()->id(), (bool) ($data['renew'] ?? false));

        return $sub->hasStatus('pendente') ? redirect()->route('billing.checkout') : redirect()->route('billing.plans')->with('success', 'Plano confirmado com sucesso.');
    }

    public function cancelPending(Request $request): RedirectResponse
    {
        $empresaId = TenantContext::empresaId();
        app(BillingAccess::class)->authorize($empresaId);
        DB::transaction(function () use ($empresaId) {
            Empresa::whereKey($empresaId)->lockForUpdate()->firstOrFail();
            $sub = Subscricao::where('empresa_id', $empresaId)->pending()->latest('id')->lockForUpdate()->first();
            if (! $sub) {
                return;
            }
            if ($sub->pagamentoOnline()->whereIn('status', ['pending', 'processing', 'paid'])->exists()) {
                throw ValidationException::withMessages(['payment' => 'O pagamento precisa de ser reconciliado antes de cancelar a contratação.']);
            }
            $sub->update(['status' => 'cancelada', 'updated_by' => auth()->id()]);
        });

        return redirect()->route('billing.plans')->with('success', 'Contratação pendente cancelada. O plano actual foi preservado.');
    }

    public function stopRenewal(): RedirectResponse
    {
        $empresaId = TenantContext::empresaId();
        app(BillingAccess::class)->authorize($empresaId);
        Subscricao::where('empresa_id', $empresaId)->effective()->update(['renovacao_automatica' => false, 'updated_by' => auth()->id()]);

        return redirect()->route('billing.plans')->with('success', 'Renovação automática desactivada. O acesso mantém-se até ao fim do período pago.');
    }

    public function receipt(int $payment): View
    {
        $empresa = TenantContext::empresa();
        abort_unless($empresa, 403);
        $payment = PagamentoOnline::where('empresa_id', $empresa->id)->where('status', 'paid')->with('subscription')->findOrFail($payment);

        return view('billing.receipt', compact('empresa', 'payment'));
    }
}
