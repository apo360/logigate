<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $id = $this->isMethod('PUT') ? $this->route('customers') : null;

        return [
            'CustomerTaxID' => ['required', 'string', 'min:6', 'max:14', Rule::unique('customers')->ignore($id, 'CustomerID')], // NIF deve ter exatamente 14 dígitos
            'AccountID' => ['nullable', 'string', 'max:30'],
            'CompanyName' => ['required', 'string', 'max:100'],
            'Telephone' => ['nullable', 'string', 'max:20'],
            'Email' => ['nullable', 'email', 'max:254'],
            'Website' => ['nullable', 'url', 'max:60'],
        ];
    }

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
            // Adicione mensagens personalizadas para outras regras de validação conforme necessário
        ];
    }
}
