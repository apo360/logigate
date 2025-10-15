<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepresentanteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

   /**
     * Regras de validação para criar/atualizar representante.
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:200',
            'apelido' => 'nullable|string|max:150',
            'telefone' => 'nullable|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'empresa_id' => 'required|exists:empresas,id',
        ];
    }

    /**
     * Mensagens personalizadas de erro.
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do representante é obrigatório.',
            'nome.max' => 'O nome não pode exceder 200 caracteres.',
            'empresa_id.required' => 'É necessário associar a uma empresa.',
            'empresa_id.exists' => 'A empresa selecionada não existe.',
        ];
    }
}
