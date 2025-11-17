<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenciamentoRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $rules = [
            'estancia_id' => 'required|exists:estancias,id',
            'referencia_cliente' => 'required|string|max:50',
            'factura_proforma' => 'required|string|max:50',
            'descricao' => 'required|string|max:150',
            'moeda' => 'required|string|max:5',
            'tipo_declaracao' => 'required|integer',
            'tipo_transporte' => 'required|integer',
            'registo_transporte' => 'nullable|string|max:150',
            'nacionalidade_transporte' => 'nullable|string|max:50',
            'manifesto' => 'nullable|string|max:30',
            'data_entrada' => 'nullable|date',
            'porto_entrada' => 'required|string|max:10',
            'peso_bruto' => 'nullable|numeric|min:0',
            'adicoes' => 'nullable|integer',
            'metodo_avaliacao' => 'required|string|max:10',
            'codigo_volume' => 'required|string|max:3',
            'qntd_volume' => 'nullable|integer|min:0',
            'forma_pagamento' => 'required|string|max:5',
            'codigo_banco' => 'required|string|max:5',
            'fob_total' => 'nullable|numeric|min:0',
            'frete' => 'nullable|numeric|min:0',
            'seguro' => 'nullable|numeric|min:0',
            'cif' => 'nullable|numeric|min:0',
            'porto_origem' => 'required|exists:portos,sigla',
            'pais_origem' => 'nullable|string'
        ];
    
        // Campos obrigatórios somente para criação
        if ($this->isMethod('post')) {
            $rules['cliente_id'] = 'required|exists:customers,id';
            $rules['exportador_id'] = 'required|exists:exportadors,id';
        }
    
        return $rules;
    }

    /**
     * Mensagens personalizadas para erros de validação.
     */
    public function messages()
    {
        return [
            'cliente_id.required' => 'O campo Cliente é obrigatório.',
            'cliente_id.exists' => 'O cliente selecionado não é válido.',
            'exportador_id.required' => 'O campo Exportador é obrigatório.',
            'exportador_id.exists' => 'O exportador selecionado não é válido.',
            'referencia_cliente.required' => 'A referência do cliente é obrigatória.',
            'factura_proforma.required' => 'A factura proforma é obrigatória.',
            'descricao.required' => 'A descrição é obrigatória.',
            'moeda.required' => 'O campo Moeda é obrigatório.',
            'tipo_declaracao.required' => 'O tipo de declaração é obrigatório.',
            'peso_bruto.required' => 'O campo Peso Bruto é obrigatório.',
            'peso_bruto.numeric' => 'O peso bruto deve ser um valor numérico.',
            'adicoes.required' => 'O campo Adições é obrigatório.',
            'metodo_avaliacao.required' => 'O campo Método de Avaliação é obrigatório.',
            'codigo_volume.required' => 'O código do volume é obrigatório.',
            'forma_pagamento.required' => 'O campo Forma de Pagamento é obrigatório.',
            'codigo_banco.required' => 'O campo Código do Banco é obrigatório.',
            'fob_total.required' => 'O campo FOB Total é obrigatório.',
            'frete.required' => 'O campo Frete é obrigatório.',
            'seguro.required' => 'O campo Seguro é obrigatório.',
            'cif.required' => 'O campo CIF é obrigatório.',
        ];
    }
}
