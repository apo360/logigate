<?php

// 'merchant_transaction_id' => 'REF' . Str::uuid()->toString(),

namespace App\Services;

use App\Models\PagamentoOnline;
use App\Models\Plano;
use App\Models\Subscricao;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use App\Services\AppyPay\AppyPayService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        protected AppyPayService $appyPay
    ) {}

    public function pay(array $data): array
    {
        foreach (['empresa','plano','cycle','method'] as $key) {
            if (!array_key_exists($key,$data)) {
                throw new \InvalidArgumentException("Missing key: {$key}");
            }
        }

        $result = DB::transaction(function () use ($data) {

            $empresa = Empresa::where('id',$data['empresa']->id)->lockForUpdate()->firstOrFail();

            $plano  = $data['plano'];
            $cycle  = $data['cycle'];
            $method = $data['method'];

            $amount = $this->resolveAmount($plano,$cycle);

            $subscription = $empresa->subscricoes()->where('status','pendente')->latest()->firstOrFail();

            // 🛡 idempotência
            $existing = PagamentoOnline::where('subscription_id',$subscription->id)
                ->whereIn('status',['pending','processing'])->lockForUpdate()->first();

            if ($existing) {
                return [
                    'method'=>$existing->method,
                    'status'=>$existing->status,
                    'payment_id'=>$existing->id,
                    'response'=>$existing->raw_response,
                    'message'=>'Pagamento já iniciado.',
                ];
            }

            $merchantId = strtoupper($method). date('ymdHis');

            $payment = PagamentoOnline::create([
                'subscription_id'=>$subscription->id,
                'method'=>$method,
                'amount'=>$amount,
                'status'=>'pending',
                'merchant_transaction_id'=>$merchantId,
            ]);

            // =====================
            // GPO
            // =====================
            if ($method === 'GPO') {

                $response = $this->appyPay->gpo([
                    'amount'=>$amount,
                    'merchantTransactionId'=>$merchantId,
                    'description'=>'Subscrição '.$plano->nome,
                    'phone'=>$data['phone'] ?? null,
                ]);

                if (!is_array($response)) {
                    Log::error('GPO_INVALID_RESPONSE', compact('response'));
                    return [
                        'status'=>'gpo_failed',
                        'ask_ref'=>true,
                        'message'=>'Falha ao iniciar pagamento por telefone.',
                    ];
                }

                $payment->update([
                    'gateway_id'=>$response['id'] ?? null,
                    'status'=>'processing',
                    'raw_response'=>$response,
                ]);

                return [
                    'method'=>'GPO',
                    'status'=>'processing',
                    'payment_id'=>$payment->id,
                    'response'=>$response,
                ];
            }

            // =====================
            // REF
            // =====================
            if ($method === 'REF') {

                $response = $this->appyPay->ref([
                    'amount'=>$amount,
                    'merchantTransactionId'=>$merchantId,
                    'description'=>'Subscrição '.$plano->nome,
                    'notify'=>[
                        'name'=>Auth::user()->name,
                        'email'=>Auth::user()->email,
                        'telephone'=>$empresa->user->phone ?? '',
                        'smsNotification'=>true,
                        'emailNotification'=>true,
                    ],
                ]);

                if (!is_array($response)) {
                    throw new \RuntimeException('REF gateway error');
                }

                $payment->update([
                    'gateway_id'=>$response['response']['id'] ?? null,
                    'status'=>'pending',
                    'raw_response'=>$response,
                ]);

                return [
                    'method'=>'REF',
                    'status'=>'pending',
                    'payment_id'=>$payment->id,
                    'response'=>$response,
                ];
            }

            // =====================
            // TRANSFER
            // =====================
            if ($method === 'TRANSFER') {

                $bankData = $this->appyPay->transfer([
                    'amount'=>$amount,
                    'subscription_id'=>$subscription->id,
                    'plan_name'=>$plano->nome,
                ]);

                $payment->update([
                    'status'=>'pending',
                    'raw_response'=>$bankData,
                ]);

                return [
                    'method'=>'TRANSFER',
                    'status'=>'pending',
                    'payment_id'=>$payment->id,
                    'bank_data'=>$bankData['bank_data'] ?? null,
                ];
            }

            throw new \InvalidArgumentException("Método inválido: {$method}");
        });

        if (!is_array($result)) {
            throw new \RuntimeException('CheckoutService::pay retornou null ou inválido');
        }

        return $result;
    }


    // 
    protected function resolveAmount(Plano $plano,string $cycle): float
    {
        return match($cycle){
            'monthly'=>$plano->preco_mensal,
            'semestral'=>$plano->preco_semestral,
            'annual'=>$plano->preco_anual,
            default=>throw new \InvalidArgumentException('Modalidade inválida'),
        };
    }

    public function checkPaymentStatus(PagamentoOnline $payment): array
    {
        return $this->appyPay->checkStatus([
            'merchantTransactionId'=>$payment->merchant_transaction_id,
        ]);
    }

    public function updateSubscription(array $data,float $amount): Subscricao
    {
        $empresa=$data['empresa'];
        $plano=$data['plano'];

        $sub=$empresa->subscricoes()
            ->where('status','pendente')
            ->firstOrFail();

        $sub->update([
            'plano_id'=>$plano->id,
            'modalidade_pagamento'=>$data['cycle'],
            'amount'=>$amount,
        ]);

        return $sub;
    }
}
