<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MercadoriaRequest extends FormRequest
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
        return [
            'Fk_Importacao' => 'nullable|integer|exists:importacoes,id',
            'Descricao' => 'required|string|max:255',
            'NCM_HS' => 'nullable|string|max:50',
            'NCM_HS_Numero' => 'nullable|string|max:50',
            'Quantidade' => 'required|numeric|min:1',
            'Qualificacao' => 'nullable|string|max:255',
            'Unidade' => 'required|string|max:50',
            'Peso' => 'required|numeric|min:0',
            'preco_unitario' => 'required|numeric|min:0',
            'preco_total' => 'required|numeric|min:0',
            'codigo_aduaneiro' => 'nullable|string|max:10',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'chassis' => 'nullable|string|max:50',
            'ano_fabricacao' => 'nullable|integer|min:1900|max:' . date('Y'),
            'potencia' => 'nullable|numeric|min:0',
            'licenciamento_id' => 'nullable|integer|exists:licenciamentos,id',
            'subcategoria_id' => 'nullable|integer|exists:sub_categoria_aduaneira,id',
        ];
    }

    public function messages()
    {
        return [
            'Descricao.required' => 'A descrição da mercadoria é obrigatória.',
            'Quantidade.required' => 'A quantidade é obrigatória.',
            'Quantidade.numeric' => 'A quantidade deve ser um número.',
            'preco_unitario.required' => 'O preço unitário é obrigatório.',
            'preco_total.required' => 'O preço total é obrigatório.',
            'Peso.required' => 'O peso é obrigatório.',
            'codigo_aduaneiro.max' => 'O código aduaneiro não pode ter mais de 10 caracteres.',
            'ano_fabricacao.integer' => 'O ano de fabricação deve ser um número inteiro.',
            'ano_fabricacao.min' => 'O ano de fabricação deve ser após 1900.',
            'ano_fabricacao.max' => 'O ano de fabricação não pode ser maior que o ano atual.',
            'subcategoria_id.exists' => 'A subcategoria selecionada é inválida.',
        ];
    }
}
