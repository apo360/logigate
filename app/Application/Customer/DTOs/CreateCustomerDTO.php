<?php

namespace App\Application\Customer\DTOs;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CreateCustomerDTO
{
    public function __construct(
        public readonly ?string $CustomerID,
        public readonly ?string $AccountID,
        public readonly string $CustomerTaxID,
        public readonly string $CompanyName,
        public readonly ?string $Telephone,
        public readonly ?string $Email,
        public readonly ?string $Website,
        public readonly ?string $SelfBillingIndicator,
        public readonly ?string $CustomerType,
        public readonly bool $is_active,
        public readonly ?string $foto,
        public readonly int $user_id,
        public readonly int $empresa_id,
        public readonly ?string $nacionality,
        public readonly ?string $doc_type,
        public readonly ?string $doc_num,
        public readonly ?Carbon $validade_date_doc,
        public readonly ?string $metodo_pagamento,
        public readonly ?string $tipo_cliente,
        public readonly ?string $tipo_mercadoria,
        public readonly ?string $frequencia,
        public readonly ?string $observacoes,
        public readonly ?string $num_licenca,
        public readonly ?Carbon $validade_licenca,
        public readonly ?string $moeda_operacao,
        public readonly array $endereco = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            CustomerID: self::nullable($data['CustomerID'] ?? null),
            AccountID: self::nullable($data['AccountID'] ?? null),
            CustomerTaxID: trim((string) ($data['CustomerTaxID'] ?? '')),
            CompanyName: trim((string) ($data['CompanyName'] ?? '')),
            Telephone: self::nullable($data['Telephone'] ?? null),
            Email: self::nullable($data['Email'] ?? null),
            Website: self::nullable($data['Website'] ?? null),
            SelfBillingIndicator: self::nullable($data['SelfBillingIndicator'] ?? null),
            CustomerType: self::nullable($data['CustomerType'] ?? null),
            is_active: (bool) ($data['is_active'] ?? true),
            foto: self::nullable($data['foto'] ?? null),
            user_id: (int) ($data['user_id'] ?? auth::id()),
            empresa_id: (int) ($data['empresa_id'] ?? Auth::user()?->empresa_id),
            nacionality: self::nullable($data['nacionality'] ?? null),
            doc_type: self::nullable($data['doc_type'] ?? null),
            doc_num: self::nullable($data['doc_num'] ?? null),
            validade_date_doc: self::date($data['validade_date_doc'] ?? null),
            metodo_pagamento: self::nullable($data['metodo_pagamento'] ?? null),
            tipo_cliente: self::nullable($data['tipo_cliente'] ?? null),
            tipo_mercadoria: self::nullable($data['tipo_mercadoria'] ?? null),
            frequencia: self::nullable($data['frequencia'] ?? null),
            observacoes: self::nullable($data['observacoes'] ?? null),
            num_licenca: self::nullable($data['num_licenca'] ?? null),
            validade_licenca: self::date($data['validade_licenca'] ?? null),
            moeda_operacao: self::nullable($data['moeda_operacao'] ?? null),
            endereco: $data['endereco'] ?? [],
        );
    }

    public function toModelArray(): array
    {
        return [
            'CustomerID' => $this->CustomerID,
            'AccountID' => $this->AccountID,
            'CustomerTaxID' => $this->CustomerTaxID,
            'CompanyName' => $this->CompanyName,
            'Telephone' => $this->Telephone,
            'Email' => $this->Email,
            'Website' => $this->Website,
            'SelfBillingIndicator' => $this->SelfBillingIndicator,
            'CustomerType' => $this->CustomerType,
            'is_active' => $this->is_active,
            'foto' => $this->foto,
            'user_id' => $this->user_id,
            'empresa_id' => $this->empresa_id,
            'nacionality' => $this->nacionality,
            'doc_type' => $this->doc_type,
            'doc_num' => $this->doc_num,
            'validade_date_doc' => $this->validade_date_doc,
            'metodo_pagamento' => $this->metodo_pagamento,
            'tipo_cliente' => $this->tipo_cliente,
            'tipo_mercadoria' => $this->tipo_mercadoria,
            'frequencia' => $this->frequencia,
            'observacoes' => $this->observacoes,
            'num_licenca' => $this->num_licenca,
            'validade_licenca' => $this->validade_licenca,
            'moeda_operacao' => $this->moeda_operacao,
        ];
    }

    private static function nullable(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' || $value === null ? null : (string) $value;
    }

    private static function date(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}