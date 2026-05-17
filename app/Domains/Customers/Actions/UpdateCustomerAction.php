<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Data\CustomerFormData;
use App\Domains\Customers\Events\CustomerUpdated;
use App\Models\Customer;

final class UpdateCustomerAction
{
    public function execute(Customer $customer, CustomerFormData $data): Customer
    {
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
            'is_active' => $payload['is_active'],
            'observacoes' => $payload['observacoes'] ?? null,
            'moeda_operacao' => $payload['moeda_operacao'] ?? null,
            'frequencia' => $payload['frequencia'] ?? null,
            'nacionality' => $payload['nacionality'] ?? null,
            'doc_type' => $payload['doc_type'] ?? null,
            'doc_num' => $payload['doc_num'] ?? null,
            'validade_date_doc' => $payload['validade_date_doc'] ?? null,
        ]);

        $customer->save();

        event(new CustomerUpdated($customer));

        return $customer;
    }
}
