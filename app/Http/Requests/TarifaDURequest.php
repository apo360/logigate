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
            'Fk_processo' => 'int',
            'lmc' => 'nullable|numeric',
            'navegacao' => 'nullable|numeric',
            'viacao' => 'nullable|numeric',
            'taxa_aeroportuaria' => 'nullable|numeric',
            'caucao' => 'nullable|numeric',
            'honorario' => 'nullable|numeric',
            'honorario_iva' => 'nullable|numeric',
            'frete' => 'nullable|numeric',
            'carga_descarga' => 'nullable|numeric',
            'orgaos_ofiais' => 'nullable|numeric',
            'deslocacao' => 'nullable|numeric',
            'guia_fiscal' => 'nullable|numeric',
            'inerentes' => 'nullable|numeric',
            'despesas' => 'nullable|numeric',
            'selos' => 'nullable|numeric',
            'NrDU' => 'nullable|string',
        ];
    }
}
