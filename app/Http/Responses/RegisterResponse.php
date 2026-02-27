<?php

namespace App\Http\Responses;

use App\Actions\Subscriptions\ActivateSubscriptionAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        $empresa = $user->empresas()->first();

        if (! $empresa) {
            return redirect()->route('dashboard');
        }

        $subscription = $empresa->subscricoes()->latest()->first();

        if (! $subscription) {
            return redirect()->route('dashboard');
        }

        // 🟢 Plano grátis
        if ($subscription->plano?->is_free) {

            app(ActivateSubscriptionAction::class)
                ->execute($subscription);

            return redirect()->route('dashboard');
        }

        // 💳 Plano pago
        if (! $user->hasActiveSubscription()) {
            return redirect()->route('checkout', [
                'conta' => $empresa->conta
            ]);
        }

        return redirect()->route('dashboard');

        /*switch ($empresa->Designacao) {
            case 'Despachante Oficial':
                return redirect()->route('dashboard');

            case 'Transitário':
                return redirect()->route('transitario.dashboard');

            case 'Agente de Carga':
                return redirect()->route('agente_carga.dashboard');

            default:
                Log::warning('Unknown designation', ['designacao' => $empresa->Designacao,]);

                return redirect('/dashboard');
        }
        */
    }
}
