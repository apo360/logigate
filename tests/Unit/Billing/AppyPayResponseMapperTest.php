<?php

namespace Tests\Unit\Billing;

use App\Domains\Billing\Enums\PaymentStatus;
use App\Infrastructure\PaymentGateways\AppyPay\AppyPayResponseMapper;
use PHPUnit\Framework\TestCase;

class AppyPayResponseMapperTest extends TestCase
{
    public function test_mapper_normalizes_ref_response(): void
    {
        $result = (new AppyPayResponseMapper())->success([
            'id' => 'gateway-1',
            'responseStatus' => [
                'status' => 'Pending',
                'message' => 'Referencia criada',
                'reference' => [
                    'entity' => '10101',
                    'referenceNumber' => '123456789',
                    'dueDate' => '2026-06-20',
                ],
            ],
        ]);

        $this->assertTrue($result->success);
        $this->assertSame(PaymentStatus::Pending, $result->status);
        $this->assertSame('gateway-1', $result->gatewayId);
        $this->assertSame('10101', $result->reference?->entity);
        $this->assertSame('123456789', $result->reference?->referenceNumber);
    }

    public function test_mapper_failure_never_returns_null(): void
    {
        $result = (new AppyPayResponseMapper())->failure('HTTP_ERROR', 'Erro');

        $this->assertFalse($result->success);
        $this->assertSame(PaymentStatus::Failed, $result->status);
        $this->assertSame('HTTP_ERROR', $result->errorCode);
    }
}
