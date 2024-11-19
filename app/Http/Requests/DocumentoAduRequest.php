<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentoAduRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'processo_id' => 'required|exists:processos,id',
            'licenciamento_id' => 'required|exists:licenciamentos,id',
            'TipoDocumento' => 'required|string|max:50',
            'NrDocumento' => 'string|max:50',
            'DataEmissao' => 'date',
            'Caminho' => 'string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'TipoDocumento.required' => 'O campo TipoDocumento é obrigatório.',
            'TipoDocumento.string' => 'O TipoDocumento deve ser uma string.',
            'TipoDocumento.max' => 'O TipoDocumento não pode ter mais de :max caracteres.',
            'NrDocumento.string' => 'O NrDocumento deve ser uma string.',
            'NrDocumento.max' => 'O NrDocumento não pode ter mais de :max caracteres.',
            'DataEmissao.required' => 'O campo DataEmissao é obrigatório.',
            'DataEmissao.date' => 'A DataEmissao deve ser uma data válida.',
            'Caminho.string' => 'O Caminho deve ser uma string.',
            'Caminho.max' => 'O Caminho não pode ter mais de :max caracteres.',
        ];
    }
}
