<?php

namespace App\Infrastructure\PaymentGateways\AppyPay;

use App\Application\Billing\DTOs\PaymentGatewayResult;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\PaymentReference;
use Carbon\Carbon;

final class AppyPayResponseMapper
{
    public function success(array $payload): PaymentGatewayResult
    {
        $response = $payload['response'] ?? $payload;
        $statusData = $response['responseStatus'] ?? $payload['responseStatus'] ?? [];
        $status = PaymentStatus::fromGateway($statusData['status'] ?? $response['status'] ?? null);
        $reference = $this->referenceFrom($response, $statusData);

        return new PaymentGatewayResult(
            true,
            $status,
            $response['id'] ?? $payload['id'] ?? null,
            $reference,
            $payload,
            $statusData['message'] ?? $response['message'] ?? null,
        );
    }

    public function failure(string $code, string $message, array $payload = []): PaymentGatewayResult
    {
        return PaymentGatewayResult::failure($code, $message, $payload);
    }

    private function referenceFrom(array $response, array $statusData): ?PaymentReference
    {
        $reference = $response['reference']
            ?? $response['paymentInfo']['reference']
            ?? $statusData['reference']
            ?? $response['paymentInfo']
            ?? [];

        $entity = $reference['entity'] ?? $reference['referenceEntity'] ?? null;
        $number = $reference['referenceNumber'] ?? $reference['reference_number'] ?? null;
        $dueDate = $reference['dueDate'] ?? $reference['referenceDueDate'] ?? null;

        if ($entity === null && $number === null && $dueDate === null) {
            return null;
        }

        return new PaymentReference(
            $entity !== null ? (string) $entity : null,
            $number !== null ? (string) $number : null,
            $dueDate ? Carbon::parse($dueDate) : null,
        );
    }
}
