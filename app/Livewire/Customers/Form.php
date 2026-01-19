<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Form extends Component
{
    public $form = [
        'CustomerTaxID' => '000000',
        'CustomerType' => '',
        'CompanyName' => '',
        'Email' => '',
        'Telephone' => '',
        'Fax' => '',
        'Website' => '',
        'SelfBillingIndicator' => '0',
        'metodo_pagamento' => '30',
        'TipoCliente' => 'Importador',
        'is_active' => 1,
        'observacoes' => '',
        'moeda_operacao' => 'AOA',
        'frequencia' => null,

        // Campos da tabela Endereço
        'AddressDetail' => '',
        'City' => '',
        'Country' => 'Angola',
        'PostalCode' => '0000-000',
        'Province' => '',
        
        // Campos para cliente individual
        'nacionality' => '',
        'doc_type' => 'BI',
        'doc_num' => '',
        'validade_date_doc' => '',
    ];

    public $empresas = [];
    public $paises = [];
    public $provincias = [];
    
    public $showDocumentSection = false;
    public $validatingNIF = false;
    public $nifValidationMessage = '';

    // Novas propriedades para o modal
    public $showNifExistsModal = false;
    public $existingCustomer = null;
    public $nifSearchResult = null;
    public $nifSearchPerformed = false;
    
    protected $rules = [
        'form.CustomerTaxID' => 'required|string|max:20',
        'form.CustomerType' => 'required|in:Individual,Empresa',
        'form.CompanyName' => 'required|string|max:255',
        'form.Email' => 'nullable|email|max:255',
        'form.Telephone' => 'required|string|max:20',
        'form.PostalCode' => 'nullable|string|max:20',
        'form.Province' => 'nullable|string|max:100',
        'form.Fax' => 'nullable|string|max:20',
        'form.Website' => 'nullable|url|max:255',
        'form.SelfBillingIndicator' => 'required|in:0,1',
        'form.metodo_pagamento' => 'nullable|in:00,15,30,45',
        'form.Address' => 'nullable|string|max:500',
        'form.City' => 'nullable|string|max:100',
        'form.Country' => 'nullable|string|max:100',
        'form.TipoCliente' => 'required|in:Importador,Exportador,Ambos',
        'form.Status' => 'required|in:Ativo,Inativo,Suspenso',
        
        // Regras condicionais para cliente individual
        'form.nacionality' => 'required_if:form.CustomerType,Individual|exists:paises,id',
        'form.doc_type' => 'required_if:form.CustomerType,Individual|in:BI,PASS,CC,CR',
        'form.doc_num' => 'required_if:form.CustomerType,Individual|string|max:50',
        'form.validade_date_doc' => 'nullable|date',
    ];
    
    protected $messages = [
        'form.CustomerTaxID.unique' => 'Este NIF já está registrado para outro cliente.',
        'form.CompanyName.required' => 'O nome da empresa é obrigatório.',
        'form.Telephone.required' => 'O telefone é obrigatório.',
        'form.nacionality.required_if' => 'A nacionalidade é obrigatória para clientes individuais.',
        'form.doc_type.required_if' => 'O tipo de documento é obrigatório para clientes individuais.',
        'form.doc_num.required_if' => 'O número do documento é obrigatório para clientes individuais.',
    ];

    public function mount()
    {
        $this->loadDependencies();
        
        // Configurar valores padrão
        $this->form['nacionality'] = Pais::where('pais', 'Angola')->value('id');
        $this->form['validade_date_doc'] = now()->addYears(5)->format('Y-m-d');
    }

    private function loadDependencies()
    {
        $this->paises = Pais::orderBy('pais')->get();
        $this->provincias = Provincia::orderBy('Nome')->get();
        $this->empresas = Empresa::active()->orderBy('Empresa')->get();
    }

    public function updatedFormCustomerTaxID($value)
    {
        if (strlen($value) >= 9) {
            $this->checkNifExists();
        }
    }

     public function checkNifExists()
    {
        $nif = $this->form['CustomerTaxID'];

        $this->nifSearchPerformed = true;
        
        // Limpar espaços e caracteres especiais
        $cleanNif = preg_replace('/[^0-9]/', '', $nif);
        
        if (strlen($cleanNif) < 9) {
            $this->nifSearchResult = ['exists' => false, 'message' => 'NIF muito curto'];
            return;
        }
        
        // Buscar cliente com este NIF
        $existing = Customer::where('CustomerTaxID', $cleanNif)->first();
        
        if ($existing) {
            $this->existingCustomer = $existing;
            $this->nifSearchResult = [
                'exists' => true,
                'customer' => $existing,
                'message' => 'Este NIF já está registrado no sistema.'
            ];
            
            // Verificar se já está associado à empresa atual
            $userEmpresaId = Auth::user()->empresas->first()->id;
            if ($userEmpresaId) {
                $alreadyAssociated = $existing->empresas()
                    ->where('empresa_id', $userEmpresaId)
                    ->exists();
                
                if ($alreadyAssociated) {
                    $this->nifSearchResult['message'] = 'Este cliente já está associado à sua empresa.';
                    $this->nifSearchResult['already_associated'] = true;
                } else {
                    // Mostrar modal para associação
                    $this->showNifExistsModal = true;
                }
            }
        } else {
            $this->nifSearchResult = [
                'exists' => false,
                'message' => 'NIF disponível para registro.'
            ];
            $this->showNifExistsModal = false;
            $this->existingCustomer = null;
        }
    }
    
    public function associateExistingCustomer()
    {
        try {
            $userEmpresaId = Auth::user()->empresas->first()->id;
            
            if (!$userEmpresaId) {
                session()->flash('error', 'Usuário não está associado a uma empresa.');
                return;
            }
            
            // Associar cliente existente à empresa do usuário
            $this->existingCustomer->empresas()->syncWithoutDetaching([$userEmpresaId]);

            
            // Fechar modal e resetar formulário
            $this->showNifExistsModal = false;
            $this->resetForm();
            
            session()->flash('success', 'Cliente associado com sucesso à sua empresa!');
            
            // Redirecionar para a página do cliente
            return redirect()->route('customers.show', $this->existingCustomer->id);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao associar cliente: ' . $e->getMessage());
        }
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'form.CustomerType') {
            $this->showDocumentSection = $this->form['CustomerType'] === 'Individual';
            
            // Limpar campos de documento se mudar para Empresa
            if (!$this->showDocumentSection) {
                $this->form['doc_type'] = 'BI';
                $this->form['doc_num'] = '';
                $this->form['validade_date_doc'] = now()->addYears(5)->format('Y-m-d');
            }
        }
        
        // Validação em tempo real do NIF
        if ($propertyName === 'form.CustomerTaxID') {
            $this->validateOnly('form.CustomerTaxID');
        }
        
        // Validação em tempo real do email
        if ($propertyName === 'form.Email') {
            $this->validateOnly('form.Email');
        }
        
        // Validação do número de documento para BI
        if ($propertyName === 'form.doc_num' && $this->form['doc_type'] === 'BI') {
            $this->validateDocumentNumber();
        }
    }

    private function validateDocumentNumber()
    {
        $docNum = $this->form['doc_num'];
        
        if ($this->form['doc_type'] === 'BI') {
            // Formato BI angolano: 9 dígitos + 2 letras + 3 dígitos
            if (!preg_match('/^\d{9}[A-Z]{2}\d{3}$/', $docNum)) {
                $this->addError('form.doc_num', 'Formato do BI inválido. Deve ser: 9 números + 2 letras maiúsculas + 3 números');
                return false;
            }
        }
        
        return true;
    }

    public function save()
    {
        // 1. Validação final do BI
        if ($this->form['CustomerType'] === 'Individual' && $this->form['doc_type'] === 'BI') {
            if (!$this->validateDocumentNumber()) {
                return;
            }
        }

        // 2. Normalizar NIF
        $cleanNif = preg_replace('/[^0-9]/', '', $this->form['CustomerTaxID']);

        // 3. Verificar NIF existente
        $existing = Customer::where('CustomerTaxID', $cleanNif)->first();
        if ($existing) {
            $this->existingCustomer = $existing;
            $this->showNifExistsModal = true;
            return;
        }

        // 4. Empresa obrigatória
        $empresa = Auth::user()->empresas->first();
        if (!$empresa) {
            session()->flash('error', 'Não é possível criar cliente sem empresa associada.');
            return;
        }

        // 5. Validação final
        $this->validate();

        try {
            DB::beginTransaction();

            // 6. Dados do cliente
            $clienteData = [
                'CustomerTaxID' => $cleanNif,
                'CustomerType' => $this->form['CustomerType'],
                'CompanyName' => $this->form['CompanyName'],
                'Email' => $this->form['Email'],
                'Telephone' => $this->form['Telephone'],
                'PostalCode' => $this->form['PostalCode'],
                'Province' => $this->form['Province'],
                'Fax' => $this->form['Fax'],
                'Website' => $this->form['Website'],
                'SelfBillingIndicator' => $this->form['SelfBillingIndicator'],
                'metodo_pagamento' => $this->form['metodo_pagamento'],
                'Address' => $this->form['Address'],
                'City' => $this->form['City'],
                'Country' => $this->form['Country'],
                'TipoCliente' => $this->form['TipoCliente'],
                'Status' => $this->form['Status'],
                'Notes' => $this->form['Notes'],
                'created_by' => Auth::id(),
            ];

            if ($this->form['CustomerType'] === 'Individual') {
                $clienteData += [
                    'nacionality' => $this->form['nacionality'],
                    'doc_type' => $this->form['doc_type'],
                    'doc_num' => $this->form['doc_num'],
                    'validade_date_doc' => $this->form['validade_date_doc'],
                ];
            }

            // 7. Criar cliente
            $cliente = Customer::create($clienteData);

            // 8. Associação obrigatória
            $cliente->empresas()->syncWithoutDetaching([
                $empresa->id => ['created_by' => auth()->id()]
            ]);

            // 9. Endereço (ajustar conforme modelo real)
            $cliente->endereco()->create([
                'AddressDetail' => $this->form['AddressDetail'],
                'AddressType' => $this->form['AddressType'],
                'Province' => $this->form['Province'],
                'City' => $this->form['City'],
                'PostalCode' => $this->form['PostalCode'],
                'Country' => $this->form['Country'],
            ]);

            DB::commit();

            session()->flash('success', 'Cliente criado com sucesso!');
            return redirect()->route('customers.show', $cliente->id);

        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao criar cliente: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.customers.form');
    }
}
