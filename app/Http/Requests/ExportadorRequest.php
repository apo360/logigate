<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ExportadorRequest extends FormRequest
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
        $id = $this->isMethod('PUT') ? $this->route('exportadors') : null;

        return [
            'ExportadorTaxID' => ['nullable', 'string', 'min:6', 'max:20'], // NIF deve ter exatamente 20 dígitos
            'AccountID' => ['nullable', 'string', 'max:30'],
            'Exportador' => ['required', 'string', 'max:100'],
            'Telefone' => ['nullable', 'string', 'max:20'], // Defina um tamanho máximo apropriado para o telefone
            'Email' => ['nullable', 'email', 'max:254'],
            'Pais' => ['required', 'numeric'],
            'Website' => ['nullable', 'url', 'max:60'], // Verifica se é uma URL válida
        ];
    }

    public function messages()
    {
        return [
            'Pais.required' => 'O campo Pais do cliExportadorente é obrigatório.',
            'Telephone.max' => 'O Telefone deve ter no máximo :max caracteres.',
            'Email.email' => 'O campo Email deve ser um endereço de email válido.',
            'Email.max' => 'O Email deve ter no máximo :max caracteres.',
            'Website.url' => 'O campo Website deve ser uma URL válida.',
            'Website.max' => 'O Website deve ter no máximo :max caracteres.',
            // Adicione mensagens personalizadas para outras regras de validação conforme necessário
        ];
    }
}
