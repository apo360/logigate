<?php

namespace App\Livewire\Customers;

use App\Application\Customer\Actions\UpdateCustomerAction;
use App\Application\Customer\DTOs\UpdateCustomerDTO;
use App\Application\Customer\Queries\BuscarCustomerQuery;
use App\Models\Customer;
use Livewire\Component;

class CustomerEdit extends Component
{
    public Customer $customer;

    public array $form = [];

    public function mount(int|Customer $customer): void
    {

        $id = $customer instanceof Customer ? $customer->id : (int) $customer;
        
        $this->customer = app(BuscarCustomerQuery::class)->execute($id);

        $this->form = [
            'CustomerID' => $this->customer->CustomerID,
            'AccountID' => $this->customer->AccountID,
            'CustomerTaxID' => $this->customer->CustomerTaxID,
            'CompanyName' => $this->customer->CompanyName,
            'Telephone' => $this->customer->Telephone,
            'Email' => $this->customer->Email,
            'Website' => $this->customer->Website,
            'SelfBillingIndicator' => $this->customer->SelfBillingIndicator,
            'CustomerType' => $this->customer->CustomerType,
            'is_active' => (bool) $this->customer->is_active,
            'foto' => $this->customer->foto,
            'nacionality' => $this->customer->nacionality,
            'doc_type' => $this->customer->doc_type,
            'doc_num' => $this->customer->doc_num,
            'validade_date_doc' => optional($this->customer->validade_date_doc)->format('Y-m-d'),
            'metodo_pagamento' => $this->customer->metodo_pagamento,
            'tipo_cliente' => $this->customer->tipo_cliente,
            'tipo_mercadoria' => $this->customer->tipo_mercadoria,
            'frequencia' => $this->customer->frequencia,
            'observacoes' => $this->customer->observacoes,
            'num_licenca' => $this->customer->num_licenca,
            'validade_licenca' => optional($this->customer->validade_licenca)->format('Y-m-d'),
            'moeda_operacao' => $this->customer->moeda_operacao,
        ];
    }

    protected function rules(): array
    {
        return [
            'form.CustomerTaxID' => ['required', 'string', 'max:50'],
            'form.CompanyName' => ['required', 'string', 'max:255'],
            'form.Telephone' => ['nullable', 'string', 'max:50'],
            'form.Email' => ['nullable', 'email', 'max:255'],
            'form.Website' => ['nullable', 'string', 'max:255'],
            'form.validade_date_doc' => ['nullable', 'date'],
            'form.validade_licenca' => ['nullable', 'date'],
        ];
    }

    public function update(UpdateCustomerAction $action)
    {
        $this->validate();

        try {
            $action->execute(
                UpdateCustomerDTO::fromArray($this->customer->id, $this->form)
            );

            session()->flash('success', 'Cliente actualizado com sucesso.');

            return redirect()->route('customers.show', $this->customer->id);
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }

        return null;
    }

    public function render()
    {
        return view('livewire.customers.customer-edit');
    }
}

