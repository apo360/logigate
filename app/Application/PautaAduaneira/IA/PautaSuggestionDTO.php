<?php

namespace App\Application\PautaAduaneira\IA;

final class PautaSuggestionDTO
{
    public function __construct(
        public readonly ?string $descricao,
        public readonly ?int $subcategoriaId,
        public readonly ?string $marca = null,
        public readonly ?string $modelo = null,
        public readonly ?string $chassis = null,
        public readonly ?string $codigoAtual = null,
        public readonly int $limit = 5,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            descricao: self::nullableString($data['descricao'] ?? null),
            subcategoriaId: self::nullableInt($data['subcategoria_id'] ?? null),
            marca: self::nullableString($data['marca'] ?? null),
            modelo: self::nullableString($data['modelo'] ?? null),
            chassis: self::nullableString($data['chassis'] ?? null),
            codigoAtual: self::nullableString($data['codigo_aduaneiro'] ?? null),
            limit: max(1, min((int) ($data['limit'] ?? 5), 10)),
        );
    }

    public function textForSearch(): string
    {
        return trim(implode(' ', array_filter([
            $this->descricao,
            $this->marca,
            $this->modelo,
            $this->codigoAtual,
        ])));
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
}
