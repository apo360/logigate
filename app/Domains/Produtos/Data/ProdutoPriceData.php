<?php

namespace App\Domains\Produtos\Data;

use Illuminate\Support\Facades\Auth;

final class ProdutoPriceData
{
    public function __construct(
        public readonly ?string $unidade,
        public readonly float $custo,
        public readonly float $venda,
        public readonly ?float $vendaSemIva,
        public readonly ?float $lucro,
        public readonly ?int $taxId,
        public readonly float $imposto,
        public readonly ?int $reasonId,
        public readonly ?float $dedutivelIva,
        public readonly ?string $motivo,
        public readonly bool $notificar,
        public readonly ?string $observacoes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $taxId = self::nullableInt($data['taxID'] ?? $data['taxa_iva'] ?? null);
        $venda = self::float($data['venda'] ?? $data['preco_venda'] ?? $data['new_price'] ?? 0);
        $custo = self::float($data['custo'] ?? $data['preco_custo'] ?? 0);
        $imposto = self::float($data['imposto'] ?? 0);

        return new self(
            unidade: self::nullableString($data['unidade'] ?? null),
            custo: $custo,
            venda: $venda,
            vendaSemIva: self::nullableFloat($data['venda_sem_iva'] ?? null),
            lucro: self::nullableFloat($data['lucro'] ?? null),
            taxId: $taxId,
            imposto: $imposto,
            reasonId: self::nullableInt($data['reasonID'] ?? null),
            dedutivelIva: self::nullableFloat($data['dedutivel_iva'] ?? null),
            motivo: self::nullableString($data['motivo'] ?? $data['motivo_alteracao'] ?? null),
            notificar: (bool) ((int) ($data['notificar'] ?? 0)),
            observacoes: self::nullableString($data['observacoes'] ?? null),
        );
    }

    public function toCreateArray(int $produtoId): array
    {
        return [
            'fk_product' => $produtoId,
            'unidade' => $this->unidade ?? 'UN',
            'custo' => $this->custo,
            'venda' => $this->venda,
            'venda_sem_iva' => $this->vendaSemIva ?? $this->venda,
            'lucro' => $this->lucro ?? ($this->venda - $this->custo),
            'taxID' => $this->taxId ?? 0,
            'imposto' => $this->imposto,
            'reasonID' => $this->reasonId ?? 0,
            'taxAmount' => 0,
            'dedutivel_iva' => $this->dedutivelIva ?? 0,
            'ativo' => true,
            'motivo_alteracao' => $this->motivo,
            'alterado_por' => Auth::id(),
            'origem' => 'Manual',
        ];
    }

    public function toUpdateArray(float $venda, ?float $vendaSemIva): array
    {
        return [
            'venda' => $venda,
            'venda_sem_iva' => $vendaSemIva ?? $venda,
            'motivo_alteracao' => $this->motivo,
            'alterado_por' => Auth::id(),
            'origem' => 'Manual',
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private static function float(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }
}
