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
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            if ($this->input('escopo', 'local') === 'local') {
                return array_intersect_key(\App\Domains\Exportadores\Services\ExportadorValidation::rules(), array_flip([
                    'codigo_exportador', 'additional_info', 'status',
                ])) + ['escopo' => ['sometimes', 'required', 'in:local,global']];
            }
        }
        return array_merge(\App\Domains\Exportadores\Services\ExportadorValidation::rules(), [
            'escopo' => ['sometimes', 'required', 'in:local,global'],
        ]);
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
