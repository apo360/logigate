<?php

namespace App\Http\Requests;

use App\Models\Porto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProcessoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verifique se o usuário tem permissão para criar/atualizar processos
        return true;
        //return auth()->check(); // Ou implemente uma lógica de permissão personalizada
    }

    /**
     * Regras de validação para a solicitação.
     */
    public function rules()
    {
        // Regras gerais
        $rules = [
            'ContaDespacho' => 'nullable|string|max:150',
            'RefCliente' => 'nullable|string|max:200',
            'estancia_id' => 'required|exists:estancias,id',
            'Descricao' => 'nullable|string|max:200',
            'DataAbertura' => 'nullable|date|before_or_equal:today',
            'DataFecho' => 'nullable|date|after:DataAbertura',
            'Estado' => 'required|string',
            'TipoProcesso' => 'required|exists:regiao_aduaneiras,id', // Deve existir na tabela `tipo_processos`
            'NrDU' => 'nullable|string|max:100',
            'N_Dar' => 'nullable|integer|min:0',
            'MarcaFiscal' => 'nullable|string|max:50',
            'BLC_Porte' => 'nullable|string|max:50',
            'Pais_origem' => 'nullable|exists:paises,id',
            'Pais_destino' => 'nullable|exists:paises,id',
            'PortoOrigem' => 'nullable',
            'DataChegada' => 'nullable|date|after_or_equal:DataAbertura',
            'TipoTransporte' => 'nullable|exists:tipo_transportes,id',
            'registo_transporte' => 'nullable|string|max:100',
            'nacionalidade_transporte' => 'nullable|string|max:50',
            'forma_pagamento' => 'required|string',
            'codigo_banco' => 'required|string',
            'Moeda' => 'nullable|string',
            'Cambio' => 'nullable|numeric|min:0',
            'ValorTotal' => 'nullable|numeric|min:0|max:999999999.99',
            'ValorAduaneiro' => 'nullable|numeric|min:0|max:999999999.99',
            'fob_total' => 'nullable|numeric|min:0|max:99999999.99',
            'frete' => 'nullable|numeric|min:0|max:99999999.99',
            'seguro' => 'nullable|numeric|min:0|max:99999999.99',
            'cif' => 'nullable|numeric|min:0|max:99999999.99',
            'peso_bruto' => 'nullable|numeric|min:0|max:99999999.99',
            'quantidade_barris' => 'nullable|integer|min:0',
            'data_carregamento' => 'nullable|date|after:DataAbertura',
            'valor_barril_usd' => 'nullable|numeric|min:0|max:99999999.99',
            'num_deslocacoes' => 'nullable|string|max:100',
            'rsm_num' => 'nullable|string|max:100',
            'certificado_origem' => 'nullable|string|max:100',
            'guia_exportacao' => 'nullable|string|max:100',
        ];

        // Regras adicionais no caso de criação
        if ($this->isMethod('post')) {
            $rules['NrProcesso'] = 'nullable|string|max:100|unique:processos,NrProcesso';
            $rules['customer_id'] = 'required|exists:customers,id';
            $rules['exportador_id'] = 'required|exists:exportadors,id';
        }

        return $rules;
    }

    /**
     * Mensagens de erro personalizadas para validação.
     */

    public function messages()
    {
        return [
            'Estado.in' => 'O estado deve ser um dos seguintes valores: Aberto, Em processamento, Concluído ou Cancelado.',
            'PortoOrigem.in' => 'O (Aero)Porto de Origem selecionado é inválido.',
            'required' => 'O campo :attribute é obrigatório.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'string' => 'O campo :attribute deve ser uma string.',
            'date' => 'O campo :attribute deve ser uma data válida.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'unique' => 'O campo :attribute de ser Único.',
            'exists' => 'O campo :attribute selecionado não é válido.'
        ];
    }

    /**
     * Filtra e sanitiza os dados antes da validação.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'Estado' => $this->Estado ?? 'Aberto', // Define um estado padrão caso não seja fornecido
            'DataAbertura' => $this->DataAbertura ?? now()->format('Y-m-d'),
        ]);
    }
}
