<?php

namespace App\Domains\FacturacaoIntegracao\Enums;

enum MetodoPagamentoHongayetuEnum: int
{
    // Valores de acordo com a documentação da API do Hongayetu
    case DINHEIRO = 0;
    case TPA = 1;
    case TRANSFERENCIA_BANCARIA = 2;
    case CARTEIRA = 3;
    case MULTICAIXA_EXPRESS = 4;
    case ATM_INTERNET_BANKING = 5;
    case CONTA_CORRENTE = 7;
    case PAYYETU_DIRECTO = 9;

    // Retorna o label do método de pagamento
    public function label(): string
    {
        return match ($this) {
            self::DINHEIRO => 'Venda à Dinheiro',
            self::TPA => 'TPA',
            self::TRANSFERENCIA_BANCARIA => 'Transferência Bancária',
            self::CARTEIRA => 'Carteira',
            self::MULTICAIXA_EXPRESS => 'Multicaixa Express',
            self::ATM_INTERNET_BANKING => 'ATM / Internet Banking',
            self::CONTA_CORRENTE => 'Conta Corrente',
            self::PAYYETU_DIRECTO => 'PayYetu Directo',
        };
    }

    // Retorna se o método de pagamento requer a seleção de um banco
    public function requiresBanco(): bool
    {
        return match ($this) {
            self::TPA,
            self::TRANSFERENCIA_BANCARIA => true,

            default => false,
        };
    }

    // Retorna se o método de pagamento aceita referência
    public function acceptsReferencia(): bool
    {
        return match ($this) {
            self::CONTA_CORRENTE => false,

            default => true,
        };
    }

    // Retorna se o método de pagamento requer a seleção de uma conta bancária
    private function requiresContaBancaria(): bool
    {
        return match ($this) {
            self::CONTA_CORRENTE => true,

            default => false,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $item) => [
                'value' => $item->value,
                'label' => $item->label(),
                'requires_banco' => $item->requiresBanco(),
                'accepts_referencia' => $item->acceptsReferencia(),
                'requires_conta_bancaria' => $item->requiresContaBancaria(),
            ])
            ->values()
            ->all();
    }
}