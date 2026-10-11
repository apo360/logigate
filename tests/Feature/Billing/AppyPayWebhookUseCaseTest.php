<?php

namespace Tests\Feature\Billing;

use App\Application\Billing\UseCases\HandleAppyPayWebhook;
use App\Http\Controllers\ArquivoController;
use App\Models\PagamentoOnline;
use App\Models\Plano;
use App\Models\Subscricao;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class AppyPayWebhookUseCaseTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('V1_TEST_DATABASE')) {
            throw new \RuntimeException('Use the disposable billing runner.');
        }
        $this->mock(ArquivoController::class)->shouldReceive('createMasterFolder')->andReturnNull();
    }

    public function test_success_webhook_marks_payment_paid(): void
    {
        [$subscription, $payment] = $this->makePendingPayment();

        $result = app(HandleAppyPayWebhook::class)->execute([
            'eventId' => 'evt-paid',
            'id' => 'gateway-paid',
            'merchantTransactionId' => $payment->merchant_transaction_id,
            'responseStatus' => ['status' => 'Success'],
        ], 'payload-paid');

        $this->assertSame('ok', $result->result);
        $this->assertDatabaseHas('pagamento_onlines', [
            'id' => $payment->id,
            'status' => 'paid',
            'gateway_id' => 'gateway-paid',
        ]);
        $this->assertNotNull($payment->refresh()->paid_at);
        $this->assertSame('ativa', $subscription->refresh()->status);
    }

    public function test_duplicate_webhook_is_idempotent(): void
    {
        [, $payment] = $this->makePendingPayment();
        $payload = [
            'eventId' => 'evt-duplicate',
            'merchantTransactionId' => $payment->merchant_transaction_id,
            'responseStatus' => ['status' => 'Pending'],
        ];

        $first = app(HandleAppyPayWebhook::class)->execute($payload, 'payload-duplicate');
        $second = app(HandleAppyPayWebhook::class)->execute($payload, 'payload-duplicate');

        $this->assertSame('ok', $first->result);
        $this->assertSame('duplicate', $second->result);
    }

    private function makePendingPayment(): array
    {
        [, $empresa] = $this->createTenant('WEBHOOK');

        $plano = Plano::create([
            'nome' => 'Plano Teste',
            'codigo' => 'TESTE',
            'descricao' => 'Plano para testes',
            'preco_mensal' => 1000,
            'preco_trimestral' => 2500,
            'preco_semestral' => 5000,
            'preco_anual' => 9000,
            'status' => 'activo',
        ]);

        $subscription = Subscricao::create([
            'empresa_id' => $empresa->id,
            'plano_id' => $plano->id,
            'modalidade_pagamento' => 'monthly',
            'data_subscricao' => now(),
            'status' => 'pendente',
        ]);

        $payment = PagamentoOnline::create([
            'empresa_id' => $empresa->id,
            'subscription_id' => $subscription->id,
            'method' => 'GPO',
            'amount' => 1140,
            'currency' => 'AOA',
            'status' => 'processing',
            'merchant_transaction_id' => 'GPO2606051200',
            'idempotency_key' => 'GPO2606051200',
        ]);

        return [$subscription, $payment];
    }
}
