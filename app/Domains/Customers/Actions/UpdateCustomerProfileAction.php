<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Data\CustomerFormData;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final readonly class UpdateCustomerProfileAction
{
    public function execute(Customer $customer, CustomerFormData $data): Customer
    {
        return DB::transaction(function () use ($customer, $data): Customer {
            $payload = $data->toArray();

            $customer->fill([
                'CustomerTaxID' => $payload['CustomerTaxID'],
                'CustomerType' => $payload['CustomerType'],
                'CompanyName' => $payload['CompanyName'],
                'Email' => $payload['Email'] ?? null,
                'Telephone' => $payload['Telephone'] ?? null,
                'Website' => $payload['Website'] ?? null,
                'SelfBillingIndicator' => $payload['SelfBillingIndicator'] ?? 0,
                'metodo_pagamento' => $payload['metodo_pagamento'] ?? null,
                'tipo_cliente' => $payload['tipo_cliente'],
                'is_active' => $payload['is_active'] ? 1 : 0,
                'observacoes' => $payload['observacoes'] ?? null,
                'moeda_operacao' => $payload['moeda_operacao'] ?? null,
                'frequencia' => $payload['frequencia'] ?? null,
                'nacionality' => $payload['nacionality'] ?? null,
                'doc_type' => $payload['doc_type'] ?? null,
                'doc_num' => $payload['doc_num'] ?? null,
                'validade_date_doc' => $payload['validade_date_doc'] ?? null,
            ]);
            $customer->save();

            if (Schema::hasTable('enderecos')) {
                $customer->endereco()->updateOrCreate(
                    ['customer_id' => $customer->id],
                    [
                        'BuildingNumber' => $payload['BuildingNumber'] ?? null,
                        'StreetName' => $payload['StreetName'] ?? null,
                        'AddressDetail' => $payload['AddressDetail'] ?? null,
                        'AddressType' => $payload['AddressType'] ?? 'Facturamento',
                        'Province' => $payload['Province'] ?? null,
                        'City' => $payload['City'] ?? null,
                        'PostalCode' => $payload['PostalCode'] ?? null,
                        'Country' => $payload['Country'] ?? 'Angola',
                    ]
                );
            }

            return $customer->refresh()->load(array_values(array_filter([
                Schema::hasTable('enderecos') ? 'endereco' : null,
                Schema::hasTable('customers_empresas') ? 'empresas' : null,
            ])));
        });
    }
}
