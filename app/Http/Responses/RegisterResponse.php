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

        if (! $user) {
            return redirect()->route('login');
        }

        $empresa = $user->empresas()->first();

        if (! $empresa) {
            Log::warning('RegisterResponse without empresa', ['user_id' => $user->id]);
            return redirect()->route('home');
        }

        $subscription = $empresa->subscricoes()
            ->latest('id')
            ->with('plano')
            ->first();

        if (! $subscription) {
            Log::warning('RegisterResponse without subscription', ['empresa_id' => $empresa->id]);
            return redirect()->route('home');
        }

        // Free plans are activated immediately so the onboarding can end on the dashboard.
        if ((bool) ($subscription->plano?->is_free ?? false)) {
            app(ActivateSubscriptionAction::class)
                ->execute($subscription);

            return redirect()->route('dashboard');
        }

        // Paid plans must complete checkout before dashboard access is allowed.
        return redirect()->route('checkout', ['conta' => $empresa->conta]);
    }
}
