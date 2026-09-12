<?php

namespace App\Livewire\Customers;

use App\Application\Customer\Actions\AssociarCustomerEmpresaAction;
use App\Application\Customer\Actions\CreateCustomerAction;
use App\Application\Customer\DTOs\AssociarCustomerEmpresaDTO;
use App\Application\Customer\DTOs\CreateCustomerDTO;
use App\Application\FacturacaoIntegracao\Actions\SincronizarClienteFacturacaoAction;
use App\Application\FacturacaoIntegracao\DTOs\SincronizarClienteFacturacaoDTO;
use App\Application\Integracoes\Services\IntegracaoResolverService;
use App\Domains\Customers\Enums\CustomerStatusEnum;
use App\Domains\Customers\Enums\CustomerEstatutoEnum;
use App\Domains\Customers\Enums\CustomerTipoDocumentoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Enums\MoedaEnum;
use App\Http\Requests\CustomerRequest;
use Livewire\Component;
use App\Models\Customer;
use App\Models\Pais;
use App\Models\Provincia;
use App\Models\EmpresaIntegracao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Form extends Component
{
    public array $form = [
        'CustomerTaxID' => '000000',
        'CustomerType' => '',
        'CompanyName' => '',
        'Email' => '',
        'Telephone' => '',
        'Fax' => '',
        'Website' => '',
        'SelfBillingIndicator' => '0',
        'metodo_pagamento' => '30',
        'TipoCliente' => CustomerEstatutoEnum::IMPORTADOR->value,
        'is_active' => 1,
        'observacoes' => '',
        'moeda_operacao' => MoedaEnum::AOA->value,
        'frequencia' => null,

        // Campos da tabela Endereço
        'AddressDetail' => '',
        'City' => '',
        'Country' => 'Angola',
        'PostalCode' => '0000-000',
        'Province' => '',
        'Address' => '',
        'AddressType' => 'Facturamento',
        
        // Campos para cliente individual
        'nacionality' => '',
        'doc_type' => CustomerTipoDocumentoEnum::BI->value,
        'doc_num' => '',
        'validade_date_doc' => '',
        'Status' => CustomerStatusEnum::ACTIVE->value,
        'Notes' => '',
    ];

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

    public bool $isIndividual = false;

    public function mount()
    {
        $this->paises = Pais::orderBy('pais')->get();
        $this->provincias = Provincia::orderBy('Nome')->get();

        // Configurar valores padrão
        $this->form['nacionality'] = Pais::where('pais', 'Angola')->value('id');
        $this->form['validade_date_doc'] = now()->addYears(5)->format('Y-m-d');
    }
        

    public function updatedFormCustomerTaxID($value)
    {
        if (strlen($value) >= 9) {
            $this->checkNifExists();
        }
    }

    public function updatedFormCustomerType($value): void
    {
        $value = trim((string) $value);

        $this->form['CustomerType'] = $value;

        $this->isIndividual = $value === 'Individual';

        if (!$this->isIndividual) {
            $this->form['nacionality'] = '';
            $this->form['doc_type'] = '';
            $this->form['doc_num'] = '';
            $this->form['validade_date_doc'] = '';
        } else {
            $this->form['doc_type'] = $this->form['doc_type'] ?: 'BI';
        }
    }

    public function setCustomerType($value): void
    {
        $this->form['CustomerType'] = $value;
        $this->showDocumentSection = $value === 'Individual';

        if (!$this->showDocumentSection) {
            $this->form['doc_type'] = CustomerTipoDocumentoEnum::BI->value;
            $this->form['doc_num'] = '';
            $this->form['validade_date_doc'] = now()->addYears(5)->format('Y-m-d');
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
    
    public function associateExistingCustomer(AssociarCustomerEmpresaAction $actionAssociate)
    {
        try {

            $empresa = Auth::user()->empresas->first();
            
            // Action para associar cliente à empresa
            $dto = new AssociarCustomerEmpresaDTO(
                customerId: $this->existingCustomer->id,
                empresaId: $empresa->id,
                pivotData: ['user_id' => Auth::id()]
            );
            $actionAssociate->execute($dto);

            
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
            $this->setCustomerType($this->form['CustomerType']);
        }

        try {
        CustomerRequest::validateLivewire($this->form);
            $this->resetErrorBag($propertyName);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
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

    public function save(CreateCustomerAction $action, SincronizarClienteFacturacaoAction $syncAction)
    {
        $this->nifSearchPerformed = true;

        try {
            $validated = CustomerRequest::validateLivewire($this->form);

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

            $customer = $action->execute(
                CreateCustomerDTO::fromArray($data)
            );

            $syncWarning = $this->syncHongayetuIfActive($customer, $empresaId, $syncAction);

            session()->flash('success', 'Cliente criado com sucesso.');

            if ($syncWarning) {
                session()->flash('warning', $syncWarning);
            }

            $this->dispatch('toast', type: 'success', message: 'Cliente criado com sucesso!');

            return redirect()->route('customers.show', $customer);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            report($e);

            Log::error('Erro ao criar cliente.', [
                'message' => $e->getMessage(),
                'form' => $this->form,
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

    private function syncHongayetuIfActive(Customer $customer, int $empresaId, SincronizarClienteFacturacaoAction $syncAction): ?string
    {
        $resolver = app(IntegracaoResolverService::class);

        if (! $resolver->isFacturacaoHongayetuActiva($empresaId)) {
            return null;
        }

        try {
            $integracao = $resolver->resolveForEmpresaId(
                $empresaId,
                TipoIntegracaoEnum::Facturacao,
                ProvedorIntegracaoEnum::HongayetuFacturacao,
            );

            $syncAction->execute($this->syncDto($customer->fresh(), $empresaId, $integracao));

            return null;
        } catch (\Throwable $exception) {
            report($exception);

            Log::warning('Cliente local criado, mas a sincronização Hongayetu falhou.', [
                'customer_id' => $customer->id,
                'empresa_id' => $empresaId,
                'message' => $exception->getMessage(),
            ]);

            return 'Cliente criado localmente, mas não foi possível sincronizar com a Facturação Hongayetu.';
        }
    }

    private function syncDto(Customer $customer, int $empresaId, EmpresaIntegracao $integracao): SincronizarClienteFacturacaoDTO
    {
        return new SincronizarClienteFacturacaoDTO(
            empresaId: $empresaId,
            empresaIntegracaoId: $integracao->id,
            customerId: $customer->id,
            nome: $customer->CompanyName,
            tipo: $this->hongayetuCustomerType($customer->CustomerType),
            nif: $customer->CustomerTaxID,
            telefone: $customer->Telephone,
            email: $customer->Email,
            endereco: $this->form['AddressDetail'] ?? $this->form['Address'] ?? null,
            provinciaId: $this->hongayetuProvinceId($this->form['Province'] ?? null),
            grupoId: $this->configuredDefaultGroupId(),
        );
    }

    private function hongayetuCustomerType(?string $type): int
    {
        $normalized = str($type ?? '')->lower()->trim()->toString();

        return in_array($normalized, ['individual', 'singular', 'pessoa singular', '0'], true) ? 0 : 1;
    }

    private function hongayetuProvinceId(?string $province): ?int
    {
        $province = trim((string) $province);

        if ($province === '') {
            return null;
        }

        if (is_numeric($province)) {
            return (int) $province;
        }

        return Provincia::query()
            ->where('Nome', $province)
            ->value('id');
    }

    private function configuredDefaultGroupId(): ?int
    {
        $groupId = config('hongayetu_facturacao.default_customer_group_id')
            ?? config('hongayetu_facturacao.grupo_id_padrao');

        return is_numeric($groupId) ? (int) $groupId : null;
    }

    public function render()
    {
        return view('livewire.customers.form');
    }
}
