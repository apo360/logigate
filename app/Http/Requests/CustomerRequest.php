<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerRequest extends FormRequest
{
    /**
     * Autoriza a request HTTP normal.
     *
     * Em Controller, podes trocar por policy depois:
     * return auth()->check() && auth()->user()->can('create', Customer::class);
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras para uso normal em Controller/FormRequest.
     */
    public function rules(): array
    {
        return self::rulesFor(
            customerId: $this->customerIdFromRoute(),
            data: $this->all()
        );
    }

    /**
     * Mensagens personalizadas.
     */
    public function messages(): array
    {
        return self::validationMessages();
    }

    /**
     * Nomes amigáveis dos campos.
     */
    public function attributes(): array
    {
        return self::validationAttributes();
    }

    /**
     * Método único para Livewire.
     *
     * Uso no componente:
     * $validated = CustomerRequest::validateLivewire($this->form, $this->customerId ?? null);
     *
     * @throws ValidationException
     */
    public static function validateLivewire(array $form, ?int $customerId = null): array
    {
        $data = self::normalize($form);

        $validator = Validator::make(
            data: $data,
            rules: self::rulesFor($customerId, $data),
            messages: self::validationMessages(),
            attributes: self::validationAttributes()
        );

        $validator->after(function ($validator) use ($customerId, $data) {
            self::validateLockedCustomerTaxId($validator, $customerId, $data);
            self::validateBiFormat($validator, $data);
        });

        try {
            return $validator->validate();
        } catch (ValidationException $e) {
            $errors = [];

            foreach ($e->errors() as $field => $messages) {
                $errors['form.' . $field] = $messages;
            }

            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Regras reutilizáveis para Controller e Livewire.
     */
    public static function rulesFor(?int $customerId = null, array $data = []): array
    {
        $data = self::normalize($data);

        $customerType = $data['CustomerType'] ?? null;

        return [
            /*
            |--------------------------------------------------------------------------
            | Dados básicos
            |--------------------------------------------------------------------------
            */

            'CustomerTaxID' => [
                'required',
                'string',
                'min:6',
                'max:14',
                Rule::unique('customers', 'CustomerTaxID')->ignore($customerId),
            ],

            'CustomerType' => [
                'required',
                Rule::in(['Individual', 'Empresa']),
            ],

            'CompanyName' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'Email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'Telephone' => [
                'required',
                'string',
                'min:7',
                'max:20',
            ],

            /*
            |--------------------------------------------------------------------------
            | Endereço / contacto
            |--------------------------------------------------------------------------
            */

            'PostalCode' => [
                'nullable',
                'string',
                'max:20',
            ],

            'Province' => [
                'nullable',
                'string',
                'max:100',
            ],

            'Fax' => [
                'nullable',
                'string',
                'max:20',
            ],

            'Website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'Address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'City' => [
                'nullable',
                'string',
                'max:100',
            ],

            'Country' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Dados fiscais / comerciais
            |--------------------------------------------------------------------------
            */

            'SelfBillingIndicator' => [
                'required',
                Rule::in(['0', '1', 0, 1]),
            ],

            'metodo_pagamento' => [
                'nullable',
                Rule::in(['00', '15', '30', '45']),
            ],

            /**
             * Atenção:
             * Na tua view actual, os valores aparecem como:
             * importador, exportador, ambos
             *
             * Por isso a validação usa lowercase.
             */
            'TipoCliente' => [
                'required',
                Rule::in(['importador', 'exportador', 'ambos']),
            ],

            /**
             * Atenção:
             * Na tua view actual, os valores aparecem como:
             * ativo, inativo
             *
             * Mantive suspenso como opção futura.
             */
            'Status' => [
                'required',
                Rule::in(['ativo', 'inativo', 'suspenso']),
            ],

            'Notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Documentação para Cliente Individual
            |--------------------------------------------------------------------------
            */

            'nacionality' => [
                Rule::requiredIf($customerType === 'Individual'),
                'nullable',
                'integer',
                Rule::exists('paises', 'id'),
            ],

            'doc_type' => [
                Rule::requiredIf($customerType === 'Individual'),
                'nullable',
                Rule::in(['BI', 'PASS', 'CC', 'CR']),
            ],

            'doc_num' => [
                Rule::requiredIf($customerType === 'Individual'),
                'nullable',
                'string',
                'max:50',
            ],

            'validade_date_doc' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    /**
     * Mensagens centralizadas.
     */
    public static function validationMessages(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | CustomerTaxID / NIF
            |--------------------------------------------------------------------------
            */

            'CustomerTaxID.required' => 'O NIF do cliente é obrigatório.',
            'CustomerTaxID.string' => 'O NIF do cliente deve ser um texto válido.',
            'CustomerTaxID.min' => 'O NIF do cliente deve ter pelo menos :min caracteres.',
            'CustomerTaxID.max' => 'O NIF do cliente deve ter no máximo :max caracteres.',
            'CustomerTaxID.unique' => 'Este NIF já está registado no sistema.',

            /*
            |--------------------------------------------------------------------------
            | Tipo de Cliente
            |--------------------------------------------------------------------------
            */

            'CustomerType.required' => 'O tipo de cliente é obrigatório.',
            'CustomerType.in' => 'O tipo de cliente deve ser Individual ou Empresa.',

            /*
            |--------------------------------------------------------------------------
            | Nome
            |--------------------------------------------------------------------------
            */

            'CompanyName.required' => 'O nome do cliente ou empresa é obrigatório.',
            'CompanyName.string' => 'O nome do cliente deve ser um texto válido.',
            'CompanyName.min' => 'O nome do cliente deve ter pelo menos :min caracteres.',
            'CompanyName.max' => 'O nome do cliente deve ter no máximo :max caracteres.',

            /*
            |--------------------------------------------------------------------------
            | Contactos
            |--------------------------------------------------------------------------
            */

            'Telephone.required' => 'O telefone é obrigatório.',
            'Telephone.string' => 'O telefone deve ser um texto válido.',
            'Telephone.min' => 'O telefone deve ter pelo menos :min caracteres.',
            'Telephone.max' => 'O telefone deve ter no máximo :max caracteres.',

            'Email.email' => 'O email informado não é válido.',
            'Email.max' => 'O email deve ter no máximo :max caracteres.',

            'Fax.max' => 'O fax deve ter no máximo :max caracteres.',

            'Website.url' => 'O website deve ser uma URL válida. Exemplo: https://empresa.com',
            'Website.max' => 'O website deve ter no máximo :max caracteres.',

            /*
            |--------------------------------------------------------------------------
            | Endereço
            |--------------------------------------------------------------------------
            */

            'PostalCode.max' => 'O código postal deve ter no máximo :max caracteres.',
            'Province.max' => 'A província deve ter no máximo :max caracteres.',
            'Address.max' => 'A morada deve ter no máximo :max caracteres.',
            'City.max' => 'A cidade deve ter no máximo :max caracteres.',
            'Country.max' => 'O país deve ter no máximo :max caracteres.',

            /*
            |--------------------------------------------------------------------------
            | Dados comerciais
            |--------------------------------------------------------------------------
            */

            'SelfBillingIndicator.required' => 'O indicador de autofaturação é obrigatório.',
            'SelfBillingIndicator.in' => 'O indicador de autofaturação deve ser Sim ou Não.',

            'metodo_pagamento.in' => 'O método de pagamento selecionado não é válido.',

            'TipoCliente.required' => 'O tipo de negócio é obrigatório.',
            'TipoCliente.in' => 'O tipo de negócio deve ser Importador, Exportador ou Ambos.',

            'Status.required' => 'O estado do cliente é obrigatório.',
            'Status.in' => 'O estado do cliente selecionado não é válido.',

            'Notes.max' => 'As observações devem ter no máximo :max caracteres.',

            /*
            |--------------------------------------------------------------------------
            | Documentos
            |--------------------------------------------------------------------------
            */

            'nacionality.required' => 'A nacionalidade é obrigatória para cliente individual.',
            'nacionality.required_if' => 'A nacionalidade é obrigatória para cliente individual.',
            'nacionality.exists' => 'A nacionalidade selecionada não é válida.',

            'doc_type.required' => 'O tipo de documento é obrigatório para cliente individual.',
            'doc_type.required_if' => 'O tipo de documento é obrigatório para cliente individual.',
            'doc_type.in' => 'O tipo de documento selecionado não é válido.',

            'doc_num.required' => 'O número do documento é obrigatório para cliente individual.',
            'doc_num.required_if' => 'O número do documento é obrigatório para cliente individual.',
            'doc_num.max' => 'O número do documento deve ter no máximo :max caracteres.',

            'validade_date_doc.date' => 'A data de validade do documento deve ser uma data válida.',
            'validade_date_doc.after_or_equal' => 'A data de validade do documento não pode ser anterior à data actual.',
        ];
    }

    /**
     * Nomes amigáveis para os campos.
     */
    public static function validationAttributes(): array
    {
        return [
            'CustomerTaxID' => 'NIF',
            'CustomerType' => 'tipo de cliente',
            'CompanyName' => 'nome do cliente',
            'Email' => 'email',
            'Telephone' => 'telefone',
            'PostalCode' => 'código postal',
            'Province' => 'província',
            'Fax' => 'fax',
            'Website' => 'website',
            'SelfBillingIndicator' => 'indicador de autofaturação',
            'metodo_pagamento' => 'método de pagamento',
            'TipoCliente' => 'tipo de negócio',
            'Status' => 'estado',
            'Address' => 'morada',
            'City' => 'cidade',
            'Country' => 'país',
            'Notes' => 'observações',
            'nacionality' => 'nacionalidade',
            'doc_type' => 'tipo de documento',
            'doc_num' => 'número do documento',
            'validade_date_doc' => 'validade do documento',
        ];
    }

    /**
     * Normalização para manter Controller e Livewire iguais.
     */
    public static function normalize(array $data): array
    {
        $normalized = $data;

        foreach ($normalized as $key => $value) {
            if (is_string($value)) {
                $normalized[$key] = trim($value);
            }
        }

        /**
         * Normalizar valores vindos de selects antigos ou inconsistentes.
         */
        if (($normalized['TipoCliente'] ?? null) === 'Importador') {
            $normalized['TipoCliente'] = 'importador';
        }

        if (($normalized['TipoCliente'] ?? null) === 'Exportador') {
            $normalized['TipoCliente'] = 'exportador';
        }

        if (($normalized['TipoCliente'] ?? null) === 'Ambos') {
            $normalized['TipoCliente'] = 'ambos';
        }

        if (($normalized['Status'] ?? null) === 'Ativo') {
            $normalized['Status'] = 'ativo';
        }

        if (($normalized['Status'] ?? null) === 'Inativo') {
            $normalized['Status'] = 'inativo';
        }

        if (($normalized['Status'] ?? null) === 'Suspenso') {
            $normalized['Status'] = 'suspenso';
        }

        /**
         * Defaults seguros.
         */
        $normalized['SelfBillingIndicator'] = $normalized['SelfBillingIndicator'] ?? '0';
        $normalized['Country'] = $normalized['Country'] ?? 'Angola';
        $normalized['Status'] = $normalized['Status'] ?? 'ativo';

        /**
         * Se for Empresa, os campos documentais não devem ser obrigatórios
         * nem poluir o payload.
         */
        if (($normalized['CustomerType'] ?? null) === 'Empresa') {
            $normalized['nacionality'] = null;
            $normalized['doc_type'] = null;
            $normalized['doc_num'] = null;
            $normalized['validade_date_doc'] = null;
        }

        return $normalized;
    }

    /**
     * Payload pronto para a tabela customers.
     *
     * Usa isto depois da validação para evitar tentar gravar campos que não existem
     * na tabela customers, como Address, City, Country, PostalCode etc.
     */
    public static function customerPayload(array $validated, ?int $empresaId = null, ?int $userId = null): array
    {
        $validated = self::normalize($validated);

        return [
            'CustomerTaxID' => $validated['CustomerTaxID'],
            'CustomerType' => $validated['CustomerType'],
            'CompanyName' => $validated['CompanyName'],
            'Email' => $validated['Email'] ?? null,
            'Telephone' => $validated['Telephone'],
            'Website' => $validated['Website'] ?? null,
            'SelfBillingIndicator' => $validated['SelfBillingIndicator'] ?? '0',

            /**
             * Mapeamento da view actual para os campos reais do teu model.
             */
            'tipo_cliente' => $validated['TipoCliente'] ?? null,
            'is_active' => ($validated['Status'] ?? 'ativo') === 'ativo',

            'nacionality' => $validated['nacionality'] ?? null,
            'doc_type' => $validated['doc_type'] ?? null,
            'doc_num' => $validated['doc_num'] ?? null,
            'validade_date_doc' => $validated['validade_date_doc'] ?? null,

            'metodo_pagamento' => $validated['metodo_pagamento'] ?? null,

            'empresa_id' => $empresaId,
            'user_id' => $userId,
        ];
    }

    /**
     * Payload separado para endereço.
     *
     * Só usa se tens relacionamento/tabela de endereço.
     */
    public static function addressPayload(array $validated): array
    {
        return [
            'Address' => $validated['Address'] ?? null,
            'City' => $validated['City'] ?? null,
            'Country' => $validated['Country'] ?? 'Angola',
            'PostalCode' => $validated['PostalCode'] ?? null,
            'Province' => $validated['Province'] ?? null,
        ];
    }

    /**
     * Evita alteração de NIF quando cliente já tem processos/licenciamentos fechados.
     *
     * Esta regra é melhor como validação "after", porque não é uma regra simples
     * de unique.
     */
    private static function validateLockedCustomerTaxId($validator, ?int $customerId, array $data): void
    {
        if (!$customerId) {
            return;
        }

        $customer = Customer::query()->find($customerId);

        if (!$customer) {
            return;
        }

        $newTaxId = $data['CustomerTaxID'] ?? null;

        if (!$newTaxId || $newTaxId === $customer->CustomerTaxID) {
            return;
        }

        if (self::customerHasLockedRelations($customer)) {
            $validator->errors()->add(
                'CustomerTaxID',
                'Não é permitido alterar o NIF porque este cliente já possui processos ou licenciamentos bloqueados/fechados.'
            );
        }
    }

    /**
     * Validação extra para BI angolano.
     */
    private static function validateBiFormat($validator, array $data): void
    {
        if (($data['CustomerType'] ?? null) !== 'Individual') {
            return;
        }

        if (($data['doc_type'] ?? null) !== 'BI') {
            return;
        }

        $docNum = $data['doc_num'] ?? null;

        if (!$docNum) {
            return;
        }

        /**
         * Formato comum:
         * 9 números + 2 letras + 3 números
         * Exemplo: 123456789LA123
         */
        if (!preg_match('/^\d{9}[A-Z]{2}\d{3}$/i', $docNum)) {
            $validator->errors()->add(
                'doc_num',
                'O número do BI deve seguir o formato: 9 números + 2 letras + 3 números. Exemplo: 123456789LA123.'
            );
        }
    }

    /**
     * Verifica relações bloqueadas de forma defensiva.
     */
    private static function customerHasLockedRelations(Customer $customer): bool
    {
        if (method_exists($customer, 'licenciamento')) {
            $query = $customer->licenciamento();

            $relatedTable = $query->getRelated()->getTable();

            if (Schema::hasColumn($relatedTable, 'status')) {
                if ((clone $query)->whereIn('status', ['fechado', 'Fechado', 'FECHADO', 'closed', 'finalizado'])->exists()) {
                    return true;
                }
            } elseif ((clone $query)->exists()) {
                return true;
            }
        }

        if (method_exists($customer, 'processos')) {
            $query = $customer->processos();

            $relatedTable = $query->getRelated()->getTable();

            if (Schema::hasColumn($relatedTable, 'status')) {
                if ((clone $query)->whereIn('status', ['fechado', 'Fechado', 'FECHADO', 'closed', 'finalizado'])->exists()) {
                    return true;
                }
            } elseif ((clone $query)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * ID vindo da rota em Controller.
     */
    private function customerIdFromRoute(): ?int
    {
        $routeCustomer = $this->route('customer');

        if ($routeCustomer instanceof Customer) {
            return $routeCustomer->id;
        }

        if (is_numeric($routeCustomer)) {
            return (int) $routeCustomer;
        }

        return null;
    }
}