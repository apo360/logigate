<?php

namespace App\Domains\Billing\Enums;

enum SubscriptionStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public static function fromPersisted(?string $status): self
    {
        return match (strtolower((string) $status)) {
            'ativa', 'active' => self::Active,
            'expirada', 'expired' => self::Expired,
            'cancelada', 'cancelled' => self::Cancelled,
            default => self::Pending,
        };
    }

    public function toPersisted(): string
    {
        return match ($this) {
            self::Pending => 'pendente',
            self::Active => 'ativa',
            self::Expired => 'expirada',
            self::Cancelled => 'cancelada',
        };
    }
}
