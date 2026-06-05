<?php

namespace App\Domains\Billing\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Paid = 'paid';
    case Failed = 'failed';
    case Expired = 'expired';

    public static function fromGateway(?string $status): self
    {
        return match (strtolower((string) $status)) {
            'success', 'paid', 'approved', 'completed' => self::Paid,
            'pending', 'waiting' => self::Pending,
            'processing', 'in_progress' => self::Processing,
            'expired' => self::Expired,
            'failed', 'error', 'declined', 'rejected' => self::Failed,
            default => self::Pending,
        };
    }

    public static function fromPersisted(?string $status): self
    {
        return match (strtolower((string) $status)) {
            'paid', 'concluido' => self::Paid,
            'processing', 'processando' => self::Processing,
            'failed', 'falhado' => self::Failed,
            'expired', 'expirado' => self::Expired,
            default => self::Pending,
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::Processing], true);
    }
}
