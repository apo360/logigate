<?php

namespace App\Domains\Exportador\Data;

final class ExportadorFormData
{
    public function __construct(
        public readonly string $Exportador,
        public readonly ?string $ExportadorTaxID,
        public readonly ?string $Telefone,
        public readonly ?string $Email,
        public readonly ?string $Pais,
        public readonly ?string $Website,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            Exportador: $data['Exportador'] ?? '',
            ExportadorTaxID: $data['ExportadorTaxID'] ?? null,
            Telefone: $data['Telefone'] ?? null,
            Email: $data['Email'] ?? null,
            Pais: $data['Pais'] ?? null,
            Website: $data['Website'] ?? null,
        );
    }
}