<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportacaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'Fk_processo' => 'required',
            'Fk_pais' => 'required',
            'PortoOrigem' => 'required|string',
            'TipoTransporte' => 'required|string',
            'NomeTransporte' => 'nullable|string',
            'DataChegada' => 'nullable|date',
            'MarcaFiscal' => 'nullable|string',
            'BLC_Porte' => 'nullable|string',
            'Moeda' => 'nullable|string',
            'Cambio' => 'nullable|numeric',
            'ValorAduaneiro' => 'nullable|numeric',
            'ValorTotal' => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'string' => 'O campo :attribute deve ser uma string.',
            'date' => 'O campo :attribute deve ser uma data válida.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
        ];
    }
}
