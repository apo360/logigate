<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TarifaDURequest extends FormRequest
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
            'processo_id' => 'int|exists:processos,id',
            'lmc' => 'nullable|numeric',
            'navegacao' => 'nullable|numeric',
            'terminal' => 'nullable|numeric',
            'porto' => 'nullable|numeric',
            'caucao' => 'nullable|numeric',
            'honorario' => 'nullable|numeric',
            'honorario_iva' => 'nullable|numeric',
            'frete' => 'nullable|numeric',
            'carga_descarga' => 'nullable|numeric',
            'direitos' => 'nullable|numeric',
            'deslocacao' => 'nullable|numeric',
            'emolumentos' => 'nullable|numeric',
            'inerentes' => 'nullable|numeric',
            'impostoEstatistico' => 'nullable|numeric',
            'iva_aduaneiro' => 'nullable|numeric',
            'iec' => 'nullable|numeric',
            'selos' => 'nullable|numeric',
            'juros_mora' => 'nullable|numeric',
            'multas' => 'nullable|numeric',
            'orgaos_ofiais' => 'nullable|numeric',
            'guia_fiscal' => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'processo_id.int' => 'O ID do processo deve ser um número inteiro.',
            'processo_id.exists' => 'O processo especificado não existe.',
            'numeric' => 'O campo :attribute deve ser um número válido.',
        ];
    }
}
