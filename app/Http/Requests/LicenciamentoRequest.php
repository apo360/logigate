<?php

namespace App\Http\Requests;

use App\Application\Licenciamento\Support\LicenciamentoFormSupport;
use App\Models\Licenciamento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class LicenciamentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        $licenciamento = $this->route('licenciamento');

        if ($licenciamento instanceof Licenciamento) {
            return Gate::allows('update', $licenciamento);
        }

        return Gate::allows('create', Licenciamento::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $empresaId = (int) $this->user()?->empresas()->value('empresas.id');

        return app(LicenciamentoFormSupport::class)->rules($empresaId);
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
