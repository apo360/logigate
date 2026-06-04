<?php

namespace App\Domains\Customers\Actions;

use App\Application\Arquivo\Actions\CriarPastaClienteAction;
use App\Domains\Customers\Data\CustomerFormData;
use App\Domains\Customers\Services\CustomerCodeGenerator;
use App\Models\Customer;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

final readonly class CreateOrAssociateCustomerAction
{
    public function __construct(
        private CustomerCodeGenerator $codeGenerator,
        private CriarPastaClienteAction $criarPastaCliente,
    ) {
    }

    public function execute(CustomerFormData $data, Empresa $empresa): Customer
    {
        return DB::transaction(function () use ($data, $empresa): Customer {
            $payload = $data->toArray();
            $customer = Customer::query()->where('CustomerTaxID', $payload['CustomerTaxID'])->first();

            if (! $customer) {
                $customer = Customer::query()->create([
                    'CustomerID' => $this->codeGenerator->generate($empresa->id, $payload['CustomerTaxID']),
                    'AccountID' => $payload['AccountID'] ?? 0,
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
                    'is_active' => $payload['is_active'] ? 1 : 0,
                    'observacoes' => $payload['observacoes'] ?? null,
                    'moeda_operacao' => $payload['moeda_operacao'] ?? null,
                    'frequencia' => $payload['frequencia'] ?? null,
                    'nacionality' => $payload['nacionality'] ?? null,
                    'doc_type' => $payload['doc_type'] ?? null,
                    'doc_num' => $payload['doc_num'] ?? null,
                    'validade_date_doc' => $payload['validade_date_doc'] ?? null,
                ]);
            }

            $customer->empresas()->syncWithoutDetaching([
                $empresa->id => [
                    'codigo_cliente' => $payload['codigo_cliente'] ?? null,
                    'status' => $payload['status'] ?? 'ativo',
                    'data_associacao' => now(),
                ],
            ]);

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

            $customer = $customer->refresh()->load(['endereco', 'empresas']);
            $this->criarPastaCliente->execute($customer, $empresa);

            return $customer;
        });
    }
}
