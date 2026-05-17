<?php

namespace App\Livewire\Forms;

use Livewire\Component;
use App\Models\Customer;
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
        'CustomerTaxID' => 'required|string|unique:customers,CustomerTaxID',
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
        
        $cliente = Customer::create([
            'CustomerTaxID' => $this->CustomerTaxID,
            'CompanyName' => $this->CompanyName,
            'Telephone' => $this->Telephone,
            'Email' => $this->Email,
            'pagamento' => $this->pagamento,
            'empresa_id' => $empresa->id,
        ]);
        
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

