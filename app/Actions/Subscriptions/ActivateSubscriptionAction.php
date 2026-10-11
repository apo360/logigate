<?php

namespace App\Actions\Subscriptions;

use App\Http\Controllers\ArquivoController;
use App\Models\Empresa;
use App\Models\Subscricao;
use App\Services\ModuloAtivacaoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivateSubscriptionAction
{
    public function execute(Subscricao $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            Empresa::whereKey($subscription->empresa_id)->lockForUpdate()->firstOrFail();
            $subscription = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->lockForUpdate()->findOrFail($subscription->id);
            // Idempotência explícita para evitar dupla ativação em callbacks repetidos.
            if ($subscription->activated_at !== null) {
                return;
            }
            if (! $subscription->plano_id && ! $subscription->commercial_snapshot) {
                throw new \LogicException('Subscrição sem contrato comercial: requer revisão antes da activação.');
            }

            if (! $subscription->hasStatus(Subscricao::STATUS_PENDENTE) && ! $subscription->hasStatus(Subscricao::STATUS_ATIVA)) {
                throw new \LogicException('Contrato cancelado ou suspenso: confirmação requer revisão financeira.');
            }
            $startsAt = data_get($subscription->commercial_snapshot, 'starts_at');
            $inicio = $startsAt && Carbon::parse($startsAt)->gt(now()) ? Carbon::parse($startsAt) : now();

            $subscription->update([
                'status' => Subscricao::STATUS_ATIVA,
                'data_inicio' => $inicio,
                'data_expiracao' => $subscription->calcularDataExpiracao($inicio),
                'activated_at' => now(),
            ]);

            app(ModuloAtivacaoService::class)
                ->ativarModulos($subscription);

            Log::info('Subscrição ativada', [
                'subscription_id' => $subscription->id,
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
