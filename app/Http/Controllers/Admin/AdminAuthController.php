<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AdminAuthController
{
    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:' . config('admin.pin_length', 6)],
        ]);

        $rateKey = 'verify-pin:' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return response()->json([
                'success' => false,
                'message' => 'Acesso temporariamente bloqueado. Tente novamente em ' . ceil($seconds / 60) . ' minutos.',
            ], 429);
        }

        $configuredSecret = config('security.admin_master_secret');

        if (blank($configuredSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Segredo administrativo não configurado.',
            ], 500);
        }

        // The secret is stored as a hash in the environment and verified in constant time.
        if (Hash::check($request->string('pin')->toString(), $configuredSecret)) {
            session([
                config('admin.session_key', 'is_admin_master') => true,
                'admin_master_authenticated_at' => now()->toIso8601String(),
            ]);

            RateLimiter::clear($rateKey);

            return response()->json([
                'success' => true,
                'redirect_url' => route('admin.dashboard'),
            ]);
        }

        RateLimiter::hit($rateKey, 60);

        $tentativasRestantes = 3 - RateLimiter::attempts($rateKey);

        return response()->json([
            'success' => false,
            'message' => 'PIN inválido. Tentativas restantes: ' . max($tentativasRestantes, 0),
        ], 401);
    }
}
