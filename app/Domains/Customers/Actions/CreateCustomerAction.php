<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Data\CustomerFormData;
use App\Domains\Customers\Events\CustomerCreated;
use App\Models\Customer;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

final class CreateCustomerAction
{
    public function execute(CustomerFormData $data, Empresa $empresa): Customer
    {
        try {
            return DB::transaction(function () use ($data, $empresa): Customer {
                $payload = $data->toArray();

                $customer = Customer::create([
                    'empresa_id' => $empresa->id,
                    'CustomerTaxID' => $payload['CustomerTaxID'],
                    'CustomerType' => $payload['CustomerType'],
                    'CompanyName' => $payload['CompanyName'],
                    'Email' => $payload['Email'] ?? null,
                    'Telephone' => $payload['Telephone'] ?? null,
                    'Website' => $payload['Website'] ?? null,
                    'SelfBillingIndicator' => $payload['SelfBillingIndicator'] ?? 0,
                    'metodo_pagamento' => $payload['metodo_pagamento'] ?? null,
                    'tipo_cliente' => $payload['tipo_cliente'],
                    'is_active' => $payload['is_active'],
                    'observacoes' => $payload['observacoes'] ?? null,
                    'moeda_operacao' => $payload['moeda_operacao'] ?? null,
                    'frequencia' => $payload['frequencia'] ?? null,
                    'nacionality' => $payload['nacionality'] ?? null,
                    'doc_type' => $payload['doc_type'] ?? null,
                    'doc_num' => $payload['doc_num'] ?? null,
                    'validade_date_doc' => $payload['validade_date_doc'] ?? null,
                ]);

                $customer->empresas()->syncWithoutDetaching([$empresa->id]);

                $customer->endereco()->create([
                    'AddressDetail' => $payload['AddressDetail'] ?? null,
                    'AddressType' => $payload['AddressType'] ?? 'Facturamento',
                    'Province' => $payload['Province'] ?? null,
                    'City' => $payload['City'] ?? null,
                    'PostalCode' => $payload['PostalCode'] ?? null,
                    'Country' => $payload['Country'] ?? 'Angola',
                ]);

                event(new CustomerCreated($customer));

                return $customer;
            });
        } catch (\Throwable $e) {
            report($e);

            throw $e;
        }
    }
}
