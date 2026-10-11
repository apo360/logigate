<?php

namespace App\Http\Responses;

use App\Actions\Subscriptions\ActivateSubscriptionAction;
use App\Application\Empresa\Actions\EstabelecerEmpresaAposAutenticacaoAction;
use App\Services\SubscriptionQuote;
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

        $empresa = app(EstabelecerEmpresaAposAutenticacaoAction::class)->execute($user);

        if (! $empresa) {
            Log::warning('RegisterResponse without empresa', ['user_id' => $user->id]);

            return redirect()->route('empresa-context.index');
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
        $quote = app(SubscriptionQuote::class)->forSubscription($subscription);
        if ($quote['is_free'] && $quote['total'] == 0) {
            app(ActivateSubscriptionAction::class)
                ->execute($subscription);

            return redirect()->route('dashboard');
        }

        // Paid plans must complete checkout before dashboard access is allowed.
        return redirect()->route('billing.checkout');
    }
}
