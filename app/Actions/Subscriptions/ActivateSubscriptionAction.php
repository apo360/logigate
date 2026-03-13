<?php

namespace App\Actions\Subscriptions;

use App\Http\Controllers\ArquivoController;
use App\Models\Subscricao;
use App\Services\ModuloAtivacaoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivateSubscriptionAction
{
    public function execute(Subscricao $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            // Idempotência explícita para evitar dupla ativação em callbacks repetidos.
            if ($subscription->activated_at !== null) {
                return;
            }

            $inicio = now();

            $subscription->update([
                'status' => Subscricao::STATUS_ATIVA,
                'data_inicio' => $inicio,
                'data_expiracao' => $subscription->calcularDataExpiracao($inicio),
                'activated_at' => now(),
            ]);

            app(ModuloAtivacaoService::class)
                ->ativarModulos(
                    $subscription->empresa_id,
                    $subscription->plano_id
                );

            Log::info('Subscrição ativada', [
                'subscription_id' => $subscription->id
            ]);

            // 📧 Futuro: Enviar o e-mail de confirmação
            // Mail::to($user->email)->send(new ConfirmationMail($otp));

            // The webhook can run outside a browser session, so file bootstrap must
            // never block subscription activation if storage setup is unavailable.
            try {
                app(ArquivoController::class)
                    ->createMasterFolder($subscription->empresa_id);
            } catch (\Throwable $exception) {
                Log::warning('SUBSCRIPTION_MASTER_FOLDER_CREATION_FAILED', [
                    'subscription_id' => $subscription->id,
                    'empresa_id' => $subscription->empresa_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        
            // Emitir factura

            // Criar registo financeiro

            // Activar funcionalidades
        });
    }
}
