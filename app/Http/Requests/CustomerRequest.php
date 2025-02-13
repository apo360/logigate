<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer; // Certifique-se de importar o modelo de clientes

class CustomerRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer essa solicitação.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Retorna as regras de validação que se aplicam à solicitação.
     */
    public function rules()
    {
        $id = $this->route('customer'); // Obtém o ID do cliente se estiver na atualização
        $empresaId = Auth::user()->empresas->first()->id;
        $customer = Customer::where('id', $id)->first(); 

        // Verifica se o cliente possui faturas ou processos fechados
        $possuiFaturasOuProcessosFechados = $customer && (
            $customer->invoices()->exists() || // Cliente tem faturas
            $customer->processos()->where('Estado', 'fechado')->exists() // Cliente tem processo fechado
        );

        return [
            'CustomerTaxID' => [
                'required',
                'string',
                'min:6',
                'max:14',
                Rule::unique('customers')->where(function ($query) use ($empresaId) {
                    return $query->where('empresa_id', $empresaId);
                })->ignore($id, 'id'),
                function ($attribute, $value, $fail) use ($customer, $possuiFaturasOuProcessosFechados) {
                    if ($customer && $possuiFaturasOuProcessosFechados && $customer->CustomerTaxID !== $value) {
                        $fail('O NIF não pode ser alterado porque o cliente possui faturas ou processos fechados.');
                    }
                }
            ],
            'AccountID' => ['nullable', 'string', 'max:30'],
            'CompanyName' => ['required', 'string', 'max:100'],
            'Telephone' => ['nullable', 'string', 'max:20'],
            'Email' => ['nullable', 'email', 'max:254'],
            'Website' => ['nullable', 'url', 'max:60'],
            'CustomerType' => 'nullable|string|in:Individual,Empresa',
            'nacionality' => 'nullable|string|max:255',
            'doc_type' => 'nullable|string|in:BI,PASS,CC,CR,Outro',
            'doc_num' => 'nullable|string|max:255',
            'validade_date_doc' => 'nullable|date|after_or_equal:today',
            'metodo_pagamento' => 'nullable|string',

            'tipo_cliente' => 'nullable|in:importador,exportador,ambos',
            'tipo_mercadoria' => 'nullable|string',
            'frequencia' => 'nullable|in:ocasional,mensal,anual',
            'observacoes' => 'nullable|string|max:500',
            'num_licenca' => 'nullable|string|max:50',
            'validade_licenca' => 'nullable|date',
            'moeda_operacao' => 'nullable|string|max:10'
        ];
    }

    /**
     * Mensagens de erro personalizadas.
     */
    public function messages()
    {
        return [
            'CustomerTaxID.required' => 'O campo NIF do cliente é obrigatório.',
            'CustomerTaxID.min' => 'O NIF do cliente deve ter pelo menos :min caracteres.',
            'CustomerTaxID.max' => 'O NIF do cliente deve ter no máximo :max caracteres.',
            'CustomerTaxID.unique' => 'O NIF do cliente já está em uso.',
            'CompanyName.required' => 'O campo Nome da Empresa é obrigatório.',
            'CompanyName.max' => 'O Nome da Empresa deve ter no máximo :max caracteres.',
            'Telephone.max' => 'O Telefone deve ter no máximo :max caracteres.',
            'Email.email' => 'O campo Email deve ser um endereço de email válido.',
            'Email.max' => 'O Email deve ter no máximo :max caracteres.',
            'Website.url' => 'O campo Website deve ser uma URL válida.',
            'Website.max' => 'O Website deve ter no máximo :max caracteres.',
        ];
    }
}
