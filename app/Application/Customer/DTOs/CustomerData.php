<?php

namespace App\Application\Customer\DTOs;

use App\Models\Customer;

class CustomerData
{
    public static function fromModel(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'CustomerID' => $customer->CustomerID,
            'AccountID' => $customer->AccountID,
            'CustomerTaxID' => $customer->CustomerTaxID,
            'CompanyName' => $customer->CompanyName,
            'Telephone' => $customer->Telephone,
            'Email' => $customer->Email,
            'Website' => $customer->Website,
            'CustomerType' => $customer->CustomerType,
            'is_active' => (bool) $customer->is_active,
            'empresa_id' => $customer->empresa_id,
            'tipo_cliente' => $customer->tipo_cliente,
            'tipo_mercadoria' => $customer->tipo_mercadoria,
            'frequencia' => $customer->frequencia,
            'observacoes' => $customer->observacoes,
            'num_licenca' => $customer->num_licenca,
            'validade_licenca' => optional($customer->validade_licenca)->format('Y-m-d'),
            'moeda_operacao' => $customer->moeda_operacao,

            'empresas' => $customer->relationLoaded('empresas')
                ? $customer->empresas->map(fn ($empresa) => [
                    'id' => $empresa->id,
                    'nome' => $empresa->nome ?? $empresa->name ?? $empresa->CompanyName ?? null,
                ])->values()->toArray()
                : [],

            'processos_count' => $customer->processos_count ?? 0,
            'licenciamentos_count' => $customer->licenciamentos_count ?? 0,
            'documentos_count' => $customer->documentos_arquivos_count ?? 0,

            'portal' => [
                'has_credentials' => method_exists($customer, 'clientePortal')
                    ? (bool) $customer->clientePortal
                    : false,
            ],
        ];
    }
}