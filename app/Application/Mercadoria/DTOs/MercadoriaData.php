<?php

namespace App\Application\Mercadoria\DTOs;

final class MercadoriaData
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $context,
        public readonly int $parentId,
        public readonly ?int $subcategoriaId,
        public readonly string $codigoAduaneiro,
        public readonly ?string $descricao,
        public readonly float $quantidade,
        public readonly float $peso,
        public readonly string $unidade,
        public readonly ?string $ncmHs,
        public readonly ?string $ncmHsNumero,
        public readonly ?string $qualificacao,
        public readonly float $precoUnitario,
        public readonly float $precoTotal,
        public readonly ?string $marca,
        public readonly ?string $modelo,
        public readonly ?string $chassis,
        public readonly ?int $anoFabricacao,
        public readonly ?float $potencia,
        public readonly ?string $pautaChangeReason = null,
        public readonly string $pautaChangeSource = 'manual',
    ) {
    }

    public static function fromLivewire(array $form, string $context, int $parentId, ?int $id = null): self
    {
        $quantidade = self::float($form['quantidade'] ?? 0);
        $precoUnitario = self::float($form['preco_unitario'] ?? 0);

        return new self(
            id: $id,
            context: $context,
            parentId: $parentId,
            subcategoriaId: self::nullableInt($form['subcategoria_id'] ?? null),
            codigoAduaneiro: trim((string) ($form['codigo_aduaneiro'] ?? '')),
            descricao: self::nullableString($form['descricao'] ?? null),
            quantidade: $quantidade,
            peso: self::float($form['peso'] ?? 0),
            unidade: trim((string) ($form['unidade'] ?? 'UN')),
            ncmHs: self::nullableString($form['ncm_hs'] ?? null),
            ncmHsNumero: self::nullableString($form['ncm_hs_numero'] ?? null),
            qualificacao: self::nullableString($form['qualificacao'] ?? null),
            precoUnitario: $precoUnitario,
            precoTotal: round($quantidade * $precoUnitario, 2),
            marca: self::nullableString($form['marca'] ?? null),
            modelo: self::nullableString($form['modelo'] ?? null),
            chassis: self::nullableString($form['chassis'] ?? null),
            anoFabricacao: self::nullableInt($form['ano_fabricacao'] ?? null),
            potencia: self::nullableFloat($form['potencia'] ?? null),
            pautaChangeReason: self::nullableString($form['pauta_change_reason'] ?? null),
            pautaChangeSource: self::normalizeSource($form['pauta_change_source'] ?? 'manual'),
        );
    }

    public function toModelAttributes(): array
    {
        $attributes = [
            'subcategoria_id' => $this->subcategoriaId,
            'codigo_aduaneiro' => $this->codigoAduaneiro,
            'Descricao' => $this->descricao,
            'Quantidade' => $this->quantidade,
            'Unidade' => $this->unidade,
            'NCM_HS' => $this->ncmHs,
            'NCM_HS_Numero' => $this->ncmHsNumero,
            'Qualificacao' => $this->qualificacao,
            'Peso' => $this->peso,
            'preco_unitario' => $this->precoUnitario,
            'preco_total' => $this->precoTotal,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'chassis' => $this->chassis,
            'ano_fabricacao' => $this->anoFabricacao,
            'potencia' => $this->potencia,
        ];

        if ($this->context === 'processo') {
            $attributes['Fk_Importacao'] = $this->parentId;
        }

        if ($this->context === 'licenciamento') {
            $attributes['licenciamento_id'] = $this->parentId;
        }

        return $attributes;
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

    private static function normalizeSource(mixed $value): string
    {
        $value = (string) $value;

        return in_array($value, ['manual', 'ai_suggestion', 'import', 'system'], true) ? $value : 'manual';
    }
}
