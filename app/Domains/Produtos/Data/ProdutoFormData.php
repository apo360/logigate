<?php

namespace App\Domains\Produtos\Data;

final class ProdutoFormData
{
    public function __construct(
        public readonly string $productType,
        public readonly string $productCode,
        public readonly string $productDescription,
        public readonly ?string $productNumberCode,
        public readonly ?int $productGroup,
        public readonly ?string $imagemPath,
        public readonly int $status,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $productCode = trim((string) ($data['ProductCode'] ?? ''));

        return new self(
            productType: trim((string) ($data['ProductType'] ?? '')),
            productCode: $productCode,
            productDescription: trim((string) ($data['ProductDescription'] ?? '')),
            productNumberCode: self::nullableString($data['ProductNumberCode'] ?? null) ?? $productCode,
            productGroup: self::nullableInt($data['ProductGroup'] ?? null),
            imagemPath: self::nullableString($data['imagem_path'] ?? null),
            status: self::nullableInt($data['status'] ?? null) ?? 1,
        );
    }

    public function toArray(): array
    {
        return [
            'ProductType' => $this->productType,
            'ProductCode' => $this->productCode,
            'ProductGroup' => $this->productGroup,
            'ProductDescription' => $this->productDescription,
            'ProductNumberCode' => $this->productNumberCode,
            'imagem_path' => $this->imagemPath,
            'status' => $this->status,
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' || $value === 'null' ? null : $value;
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 'null') {
            return null;
        }

        return (int) $value;
    }
}
