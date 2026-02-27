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

            // 🔐 Idempotência definitiva
            if ($subscription->activated_at !== null) {
                return;
            }

            $duracao = match ($subscription->modalidade_pagamento) {
                'monthly' => 1,
                'trimestral' => 3,
                'semestral' => 6,
                'annual' => 12,
                default => 1
            };

            $inicio = now();

            $subscription->update([
                'status' => 'ATIVA',
                'data_inicio' => $inicio,
                'data_expiracao' => $inicio->copy()->addMonths($duracao),
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

            // Activar e criar pasta principal no S3
            app(ArquivoController::class)->
                createMasterFolder(
                    $subscription->empresa_id
                );
        
            // Emitir factura

            // Criar registo financeiro

            // Activar funcionalidades
        });
    }
}