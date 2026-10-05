<?php
namespace App\Imports;

use App\Application\Importacao\RowsImport;
use App\Application\Customer\Actions\CreateCustomerAction;
use App\Application\Customer\DTOs\CreateCustomerDTO;
use App\Http\Requests\CustomerRequest;

class CustomersImport extends RowsImport
{
    protected function fields(): array
    {
        return ['CustomerTaxID', 'CompanyName', 'CustomerType', 'Telephone', 'Email', 'Website', 'AccountID', 'SelfBillingIndicator', 'TipoCliente', 'Status'];
    }
    protected function create(array $data): int
    {
        foreach (['CustomerTaxID', 'Telephone', 'AccountID'] as $field) {
            if (isset($data[$field])) { $data[$field] = (string) $data[$field]; }
        }
        // Locate globally only to reuse the normal association path; never update a shared profile.
        $existing = \App\Models\Customer::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->where('CustomerTaxID', $data['CustomerTaxID'] ?? '')->first();
        if ($existing && ! $this->empresa->customers()->where('customers.id', $existing->id)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['CustomerTaxID' => 'NIF indisponível para importação. Use o fluxo autorizado de associação de clientes.']);
        }
        $validated = CustomerRequest::validateLivewire($data, $existing?->id);
        $validated['tipo_cliente'] = $validated['TipoCliente'];
        $validated['is_active'] = ($validated['Status'] ?? 'ativo') === 'ativo';
        $dto = CreateCustomerDTO::fromArray($validated + ['empresa_id' => $this->empresa->id, 'user_id' => $this->actor->id]);
        return app(CreateCustomerAction::class)->execute($dto)->id;
    }
}
