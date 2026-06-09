<?php

namespace App\Livewire\Empresa;

use App\Actions\Subscriptions\ActivateSubscriptionAction;
use App\Domains\Billing\Enums\BillingCycle;
use App\Models\Plano;
use App\Models\Subscricao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EmpresaSubscricao extends Component
{
    public function index()
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            abort(403, 'Nenhuma empresa associada.');
        }

        $activeSubscription = $empresa->subscricoes()
            ->active()
            ->where(function ($query) {
                $query->whereNull('data_expiracao')
                    ->orWhere('data_expiracao', '>', now());
            })
            ->latest('id')
            ->first();

        if ($activeSubscription) {
            return redirect()->route('dashboard');
        }

        $pendingSubscription = $empresa->subscricoes()
            ->pending()
            ->latest('id')
            ->first();

        if ($pendingSubscription) {
            return redirect()->route('checkout', ['conta' => $empresa->conta]);
        }

        $planos = Plano::query()
            ->where('status', 'activo')
            ->orderBy('ordem')
            ->orderBy('preco_mensal')
            ->get();

        return view('billing.plans', compact('empresa', 'planos'));
    }

    public function start(Request $request)
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            abort(403, 'Nenhuma empresa associada.');
        }

        $validated = $request->validate([
            'plano_id' => ['required', 'exists:planos,id'],
            'modalidade_pagamento' => ['required', 'in:monthly,semestral,annual'],
        ]);

        $plano = Plano::findOrFail($validated['plano_id']);
        $cycle = BillingCycle::fromInput($validated['modalidade_pagamento']);
        $price = $cycle->priceFrom($plano);

        return DB::transaction(function () use ($empresa, $plano, $cycle, $price) {
            $activeSubscription = $empresa->subscricoes()
                ->active()
                ->where(function ($query) {
                    $query->whereNull('data_expiracao')
                        ->orWhere('data_expiracao', '>', now());
                })
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($activeSubscription) {
                return redirect()->route('dashboard');
            }

            $pendingSubscription = $empresa->subscricoes()
                ->pending()
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if (! $pendingSubscription) {
                $pendingSubscription = Subscricao::create([
                    'empresa_id' => $empresa->id,
                    'plano_id' => $plano->id,
                    'modalidade_pagamento' => $cycle->value,
                    'valor_pago' => $price,
                    'data_subscricao' => now(),
                    'status' => Subscricao::STATUS_PENDENTE,
                    'created_by' => Auth::id(),
                ]);
            }

            if ((bool) ($plano->is_free ?? false) || $price <= 0) {
                app(ActivateSubscriptionAction::class)->execute($pendingSubscription);

                return redirect()
                    ->route('dashboard')
                    ->with('success', 'Plano gratuito ativado com sucesso.');
            }

            return redirect()->route('checkout', ['conta' => $empresa->conta]);
        });
    }
    
    public function render()
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            abort(403, 'Nenhuma empresa associada.');
        }

        $planos = Plano::query()
            ->where('status', 'activo')
            ->orderBy('ordem')
            ->orderBy('preco_mensal')
            ->get();

        return view('livewire.empresa.empresa-subscricao', compact('empresa', 'planos'));
    }
}
