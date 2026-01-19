<?php

namespace App\Http\Livewire\Forms;

use Livewire\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class ClienteQuickForm extends Component
{
    public $CompanyName = '';
    public $CustomerTaxID = '';
    public $Telephone = '';
    public $Email = '';
    public $modalType;
    
    protected $rules = [
        'CompanyName' => 'required|min:3',
        'CustomerTaxID' => 'nullable|string|max:20',
        'Telephone' => 'nullable|string|max:20',
        'Email' => 'nullable|email',
    ];

    public function save()
    {
        $this->validate();
        
        try {
            $existing = Customer::where('CompanyName', $this->CompanyName)->first();
            
            if ($existing) {
                session()->flash('error', 'Já existe um cliente com este nome.');
                return;
            }
            
            $cliente = Customer::create([
                'CompanyName' => $this->CompanyName,
                'CustomerTaxID' => $this->CustomerTaxID,
                'Telephone' => $this->Telephone,
                'Email' => $this->Email,
                'created_by' => auth()->id(),
            ]);

            // CORREÇÃO: dispatch() em vez de emit()
            $this->dispatch('closeQuickModal');
            $this->dispatch('clienteCreated', [
                'id' => $cliente->id,
                'name' => $cliente->CompanyName,
            ]);

            $this->reset(['CompanyName', 'CustomerTaxID', 'Telephone', 'Email']);
            
            session()->flash('success', 'Cliente criado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao criar cliente', ['error' => $e->getMessage()]);
            session()->flash('error', 'Erro ao criar cliente: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        // CORREÇÃO: dispatch() em vez de emit()
        $this->dispatch('closeQuickModal');
    }

    public function render()
    {
        return view('livewire.forms.cliente-quick-form');
    }
}