<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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

        $empresaId = Auth::user()->empresas->first()->id;

        return [
            'ExportadorTaxID' => [
                'nullable',
                'string',
                'min:6',
                'max:20',
                Rule::unique('exportadores')->where(function ($query) use ($empresaId) {
                    return $query->where('empresa_id', $empresaId);
                })->ignore($this->route('exportador'), 'ExportadorID')
            ],
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
