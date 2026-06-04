<?php

namespace App\Domains\Exportadores\Data;

final class ExportadorFormData
{
    public function __construct(
        public readonly string $exportador,
        public readonly ?string $exportadorTaxId,
        public readonly ?string $accountId,
        public readonly ?string $endereco,
        public readonly ?string $telefone,
        public readonly ?string $email,
        public readonly ?string $pais,
        public readonly ?string $website,
        public readonly ?string $cidade,
        public readonly ?string $codigoExportador,
        public readonly ?string $additionalInfo,
        public readonly string $status,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            exportador: trim((string) ($data['Exportador'] ?? '')),
            exportadorTaxId: self::nullableString($data['ExportadorTaxID'] ?? null),
            accountId: self::nullableString($data['AccountID'] ?? null),
            endereco: self::nullableString($data['Endereco'] ?? null),
            telefone: self::nullableString($data['Telefone'] ?? null),
            email: self::nullableString($data['Email'] ?? null),
            pais: self::nullableString($data['Pais'] ?? null),
            website: self::nullableString($data['Website'] ?? null),
            cidade: self::nullableString($data['Cidade'] ?? null),
            codigoExportador: self::nullableString($data['codigo_exportador'] ?? null),
            additionalInfo: self::nullableString($data['additional_info'] ?? null),
            status: self::normalizeStatus($data['status'] ?? 'ATIVO'),
        );
    }

    public function globalAttributes(): array
    {
        return [
            'Exportador' => $this->exportador,
            'ExportadorTaxID' => $this->exportadorTaxId,
            'AccountID' => $this->accountId,
            'Endereco' => $this->endereco,
            'Telefone' => $this->telefone,
            'Email' => $this->email,
            'Pais' => $this->pais,
            'Website' => $this->website,
            'Cidade' => $this->cidade,
        ];
    }

    public function associationAttributes(): array
    {
        return [
            'codigo_exportador' => $this->codigoExportador,
            'additional_info' => $this->additionalInfo,
            'status' => $this->status,
        ];
    }

    public function toArray(): array
    {
        return array_merge($this->globalAttributes(), $this->associationAttributes());
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function normalizeStatus(mixed $value): string
    {
        $value = strtoupper(trim((string) $value));

        return $value === 'INATIVO' ? 'INATIVO' : 'ATIVO';
    }
}
