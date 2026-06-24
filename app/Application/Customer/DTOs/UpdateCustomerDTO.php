<?php

namespace App\Application\Customer\DTOs;

use Illuminate\Support\Carbon;

class UpdateCustomerDTO
{
    public function __construct(
        public readonly int $id,
        public readonly array $data,
        public readonly array $endereco = [],
    ) {
    }

    public static function fromArray(int $id, array $data): self
    {
        $allowed = [
            'CustomerID',
            'AccountID',
            'CustomerTaxID',
            'CompanyName',
            'Telephone',
            'Email',
            'Website',
            'SelfBillingIndicator',
            'CustomerType',
            'is_active',
            'foto',
            'user_id',
            'empresa_id',
            'nacionality',
            'doc_type',
            'doc_num',
            'validade_date_doc',
            'metodo_pagamento',
            'tipo_cliente',
            'tipo_mercadoria',
            'frequencia',
            'observacoes',
            'num_licenca',
            'validade_licenca',
            'moeda_operacao',
        ];

        $clean = [];

        foreach ($allowed as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            if (in_array($field, ['validade_date_doc', 'validade_licenca'], true)) {
                $clean[$field] = self::date($value);
                continue;
            }

            if ($field === 'is_active') {
                $clean[$field] = (bool) $value;
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            $clean[$field] = $value === '' ? null : $value;
        }

        return new self(
            id: $id,
            data: $clean,
            endereco: $data['endereco'] ?? [],
        );
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