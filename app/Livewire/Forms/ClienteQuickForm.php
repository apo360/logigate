<?php

namespace App\Livewire\Forms;

use App\Domains\Customers\Actions\CreateOrAssociateCustomerAction;
use App\Domains\Customers\Data\CustomerFormData;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ClienteQuickForm extends Component
{
    public $showModal = false;
    
    public $CustomerTaxID = '';
    public $CompanyName = '';
    public $Telephone = '';
    public $Email = '';
    public $pagamento = '';
    
    protected $rules = [
        'CustomerTaxID' => 'required|string',
        'CompanyName' => 'required|string',
        'Telephone' => 'required|string',
        'Email' => 'nullable|email',
        'pagamento' => 'nullable|string',
    ];
    
    protected $listeners = ['abrirModalCliente' => 'open'];
    
    public function open()
    {
        $this->reset();
        $this->resetValidation();
        $this->showModal = true;
    }
    
    public function close()
    {
        $this->showModal = false;
    }
    
    public function save()
    {
        $this->validate();
        
        $empresa = Auth::user()->empresas->first(); // ajuste conforme sua lógica
        
        $cliente = app(CreateOrAssociateCustomerAction::class)->execute(CustomerFormData::fromArray([
            'CustomerTaxID' => $this->CustomerTaxID,
            'CustomerType' => 'Empresa',
            'CompanyName' => $this->CompanyName,
            'Telephone' => $this->Telephone,
            'Email' => $this->Email,
            'metodo_pagamento' => $this->pagamento,
            'TipoCliente' => 'Importador',
            'Status' => 'Ativo',
        ]), $empresa);
        
        // Dispara evento para o formulário principal atualizar a lista e selecionar o novo cliente
        $this->dispatch('clienteCriado', clienteId: $cliente->id, nome: $cliente->CompanyName);
        
        $this->close();
        session()->flash('message', 'Cliente criado com sucesso!');
    }
    
    public function render()
    {
        return view('livewire.forms.cliente-quick-form', [
            'showModal' => $this->showModal,
        ]);
    }
}
