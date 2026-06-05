<?php

namespace App\Domains\Billing\Enums;

use App\Models\Plano;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Trimestral = 'trimestral';
    case Semestral = 'semestral';
    case Annual = 'annual';

    public static function fromInput(?string $value): self
    {
        return match (strtolower(trim((string) $value))) {
            'monthly', 'mensal' => self::Monthly,
            'trimestral', 'quarterly' => self::Trimestral,
            'semestral' => self::Semestral,
            'annual', 'anual' => self::Annual,
            default => throw new \InvalidArgumentException('Modalidade de pagamento invalida.'),
        };
    }

    public function priceFrom(Plano $plan): float
    {
        return (float) match ($this) {
            self::Monthly => $plan->preco_mensal,
            self::Trimestral => $plan->preco_trimestral,
            self::Semestral => $plan->preco_semestral,
            self::Annual => $plan->preco_anual,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Mensal',
            self::Trimestral => 'Trimestral',
            self::Semestral => 'Semestral',
            self::Annual => 'Anual',
        };
    }
}
