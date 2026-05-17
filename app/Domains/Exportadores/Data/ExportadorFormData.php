<?php

namespace App\Domains\Exportadores\Data;

final class ExportadorFormData
{
    public function __construct(
        public readonly string $exportador,
        public readonly ?string $exportadorTaxId,
        public readonly ?string $telefone,
        public readonly ?string $email,
        public readonly ?string $pais,
        public readonly ?string $website,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            exportador: trim((string) ($data['Exportador'] ?? '')),
            exportadorTaxId: self::nullableString($data['ExportadorTaxID'] ?? null),
            telefone: self::nullableString($data['Telefone'] ?? null),
            email: self::nullableString($data['Email'] ?? null),
            pais: self::nullableString($data['Pais'] ?? null),
            website: self::nullableString($data['Website'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'Exportador' => $this->exportador,
            'ExportadorTaxID' => $this->exportadorTaxId,
            'Telefone' => $this->telefone,
            'Email' => $this->email,
            'Pais' => $this->pais,
            'Website' => $this->website,
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
}
