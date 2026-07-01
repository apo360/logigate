<?php

namespace App\Livewire\Forms;

use App\Application\Customer\Actions\CreateCustomerAction;
use App\Application\Customer\DTOs\CreateCustomerDTO;
use App\Http\Requests\CustomerRequest;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ClienteQuickForm extends Component
{
    public $showModal = false;
    
    public $CustomerTaxID = '';
    public $CompanyName = '';
    public $Telephone = '';
    public $Email = '';
    public $pagamento = '00';
    
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
    
    public function save(CreateCustomerAction $action)
    {
        try {
            $this->validate();

            $validated = CustomerRequest::normalize([
                'CustomerTaxID' => $this->CustomerTaxID,
                'CustomerType' => 'Empresa',
                'CompanyName' => $this->CompanyName,
                'Telephone' => $this->Telephone,
                'Email' => $this->Email,
                'SelfBillingIndicator' => '0',
                'metodo_pagamento' => $this->pagamento ?: '00',
                'TipoCliente' => 'importador',
                'Status' => 'ativo',
            ]);

            $empresaId = $this->currentEmpresaId();

            $payload = CustomerRequest::customerPayload(
                validated: $validated,
                empresaId: $empresaId,
                userId: Auth::id()
            );

            $data = array_merge($validated, $payload, [
                'user_id' => Auth::id(),
                'empresa_id' => $empresaId,
            ]);
            
            $customer = $action->execute(CreateCustomerDTO::fromArray($data));
            
            session()->flash('success', 'Cliente criado com sucesso.');

            $this->dispatch('clienteCriado', clienteId: $customer->id, nome: $customer->CompanyName);
            $this->dispatch('toast', type: 'success', message: 'Cliente criado com sucesso!');

            $this->close();
            $this->reset(['CustomerTaxID', 'CompanyName', 'Telephone', 'Email']);
            $this->pagamento = '00';

            return null;

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            report($e);

            Log::error('Erro ao criar cliente.', [
                'message' => $e->getMessage(),
                'form' => [
                    'CustomerTaxID' => $this->CustomerTaxID,
                    'CompanyName' => $this->CompanyName,
                    'Telephone' => $this->Telephone,
                    'Email' => $this->Email,
                    'pagamento' => $this->pagamento,
                ],
                'user_id' => Auth::id(),
            ]);

            session()->flash('error', 'Erro ao criar cliente: ' . $e->getMessage());

            $this->dispatch('toast', type: 'error', message: 'Erro ao criar cliente: ' . $e->getMessage());
        }

        return null;
    }

    private function currentEmpresaId(): int
    {
        $empresaId = Auth::user()->empresa_id
            ?? Auth::user()->empresas()->value('empresas.id');

        if (!$empresaId) {
            throw new \RuntimeException('Nenhuma empresa activa foi encontrada para o utilizador autenticado.');
        }

        return (int) $empresaId;
    }
    
    public function render()
    {
        return view('livewire.forms.cliente-quick-form', [
            'showModal' => $this->showModal,
        ]);
    }
}
