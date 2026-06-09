<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        $empresa = $user->empresas->first();

        if (! $empresa) {
            Log::warning('User without company', ['user_id' => $user->id]);
            return redirect('/dashboard');
        }

        if (! $user->hasActiveSubscription()) {
            $pendingSubscription = $empresa->subscricoes()
                ->pending()
                ->latest('id')
                ->first();

            if ($pendingSubscription) {
                return redirect()->route('checkout', ['conta' => $empresa->conta]);
            }

            return redirect()->route('billing.plans');
        }

        switch ($empresa->Designacao) {
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
    }
}
