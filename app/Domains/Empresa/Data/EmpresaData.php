<?php

namespace App\Domains\Empresa\Data;

final class EmpresaData
{
    public function __construct(
        public readonly ?string $codFactura = null,
        public readonly ?string $codProcesso = null,
        public readonly ?string $empresa = null,
        public readonly ?string $actividadeComercial = null,
        public readonly ?string $designacao = null,
        public readonly ?string $nif = null,
        public readonly ?string $cedula = null,
        public readonly ?string $slogan = null,
        public readonly ?string $enderecoCompleto = 'Desconhecido',
        public readonly ?string $provincia = null,
        public readonly ?string $cidade = null,
        public readonly ?string $dominio = null,
        public readonly ?string $email = null,
        public readonly ?string $fax = null,
        public readonly ?string $contactoMovel = null,
        public readonly ?string $contactoFixo = null,
        public readonly ?string $sigla = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            codFactura: self::nullableString($data['CodFactura'] ?? null),
            codProcesso: self::nullableString($data['CodProcesso'] ?? null),
            empresa: self::nullableString($data['Empresa'] ?? null),
            actividadeComercial: self::nullableString($data['ActividadeComercial'] ?? null),
            designacao: self::nullableString($data['Designacao'] ?? null),
            nif: self::nullableString($data['NIF'] ?? null),
            cedula: self::nullableString($data['Cedula'] ?? null),
            slogan: self::nullableString($data['Slogan'] ?? null),
            enderecoCompleto: self::nullableString($data['Endereco_completo'] ?? 'Desconhecido'),
            provincia: self::nullableString($data['Provincia'] ?? null),
            cidade: self::nullableString($data['Cidade'] ?? null),
            dominio: self::nullableString($data['Dominio'] ?? null),
            email: self::nullableString($data['Email'] ?? null),
            fax: self::nullableString($data['Fax'] ?? null),
            contactoMovel: self::nullableString($data['Contacto_movel'] ?? null),
            contactoFixo: self::nullableString($data['Contacto_fixo'] ?? null),
            sigla: self::nullableString($data['Sigla'] ?? null),
        );
    }

    public function toAttributes(): array
    {
        return array_filter([
            'CodFactura' => $this->codFactura,
            'CodProcesso' => $this->codProcesso,
            'Empresa' => $this->empresa,
            'ActividadeComercial' => $this->actividadeComercial,
            'Designacao' => $this->designacao,
            'NIF' => $this->nif,
            'Cedula' => $this->cedula,
            'Slogan' => $this->slogan,
            'Endereco_completo' => $this->enderecoCompleto,
            'Provincia' => $this->provincia,
            'Cidade' => $this->cidade,
            'Dominio' => $this->dominio,
            'Email' => $this->email,
            'Fax' => $this->fax,
            'Contacto_movel' => $this->contactoMovel,
            'Contacto_fixo' => $this->contactoFixo,
            'Sigla' => $this->sigla,
        ], fn ($value) => $value !== null);
    }

    // Função From para criar uma instância de EmpresaData a partir de um array
    public static function from(array $data): self
    {
        return self::fromArray([
            'CodFactura' => $data['CodFactura'] ?? null,
            'CodProcesso' => $data['CodProcesso'] ?? null,
            'Empresa' => $data['Empresa'] ?? null,
            'ActividadeComercial' => $data['ActividadeComercial'] ?? null,
            'Designacao' => $data['Designacao'] ?? null,
            'NIF' => $data['NIF'] ?? null,
            'Cedula' => $data['Cedula'] ?? null,
            'Slogan' => $data['Slogan'] ?? null,
            'Endereco_completo' => $data['Endereco_completo'] ?? 'Desconhecido',
            'Provincia' => $data['Provincia'] ?? null,
            'Cidade' => $data['Cidade'] ?? null,
            'Dominio' => $data['Dominio'] ?? null,
            'Email' => $data['Email'] ?? null,
            'Fax' => $data['Fax'] ?? null,
            'Contacto_movel' => $data['Contacto_movel'] ?? null,
            'Contacto_fixo' => $data['Contacto_fixo'] ?? null,
            'Sigla' => $data['Sigla'] ?? null,
        ]);
    }

    // Função auxiliar para tratar valores nulos e strings vazias
    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
