<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AdminAuthController extends Controller
{
    public function verifyPin(Request $request)
    {
        // 🔹 1. Validação do campo PIN
        $request->validate([
            'pin' => ['required', 'string', 'size:' . config('admin.pin_length', 6)], // valor padrão = 6
        ]);

        // 🔹 2. Cria uma "chave" única para controle de tentativas por IP
        $rateKey = 'verify-pin:' . $request->ip();

        // 🔹 3. Bloqueia se excedeu o limite
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return response()->json([
                'success' => false,
                'message' => 'Acesso temporariamente bloqueado. Tente novamente em ' . ceil($seconds / 60) . ' minutos.',
            ], 429);
        }

        // 🔹 4. Lógica de verificação do PIN
        // Para desenvolvimento, vamos aceitar o PIN 1234
        $pinInserido = $request->input('pin');
        $pinValido = '123456';

        // Alternativamente: usar hash no .env
        // if (password_verify($pinInserido, env('ADMIN_MASTER_PIN_HASH'))) { ... }

        if ($pinInserido === $pinValido) {
            // 🔹 5. Autentica e guarda sessão
            session(['admin_logged_in' => true]);

            // 🔹 6. Limpa tentativas falhadas
            RateLimiter::clear($rateKey);

            return response()->json([
                'success' => true,
                'redirect_url' => route('admin.dashboard'),
            ]);
        }

        // 🔹 7. Incrementa tentativas falhadas
        RateLimiter::hit($rateKey, 60); // bloqueio de 60 segundos

        $tentativasRestantes = 3 - RateLimiter::attempts($rateKey);

        return response()->json([
            'success' => false,
            'message' => 'PIN inválido. Tentativas restantes: ' . max($tentativasRestantes, 0),
        ], 401);
    }
}
