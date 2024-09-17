<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServicoProdutoRequest extends FormRequest
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
            'ProductType' => 'required|in:P,S,O,E,I',
            'ProductCode' => 'required|string|max:60',
            'ProductDescription' => 'required|string|max:200',
            'ProductNumberCode' => 'nullable|string',
            'ProductGroup' => 'nullable|integer',
            'unidade' => 'nullable|string',
            'imagem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'reasonID' => 'nullable|integer',
            'custo' => 'nullable|numeric',
            'venda' => 'nullable|numeric',
            'lucro' => 'nullable|numeric',
            'venda_sem_iva' => 'nullable|numeric',
            'dedutivel_iva' => 'nullable|numeric|min:0|max:100', // Permite valores entre 0 e 100
            'imposto' => 'nullable|numeric',
            'taxa_iva' => 'required|exists:tax_tables,id'
        ];
    }

    public function message()
    {
        return [
            'ProductType.required' => 'O tipo de produto é obrigatório.',
            'ProductType.in' => 'O tipo de produto deve ser um dos seguintes valores: P, S, O, E, I.',
            'ProductCode.required' => 'O código do produto é obrigatório.',
            'ProductCode.string' => 'O código do produto deve ser um texto.',
            'ProductCode.max' => 'O código do produto não pode ter mais do que 60 caracteres.',
            'ProductDescription.required' => 'A descrição do produto é obrigatória.',
            'ProductDescription.string' => 'A descrição do produto deve ser um texto.',
            'ProductDescription.max' => 'A descrição do produto não pode ter mais do que 200 caracteres.',
            'ProductNumberCode.string' => 'O código numérico do produto deve ser um texto.',
            'ProductGroup.integer' => 'O grupo do produto deve ser um número inteiro.',
            'unidade.string' => 'A unidade do produto deve ser um texto.',
            'imagem.image' => 'A imagem do produto deve ser um arquivo de imagem.',
            'imagem.mimes' => 'A imagem do produto deve ser dos tipos: jpeg, png, jpg ou gif.',
            'imagem.max' => 'A imagem do produto não pode ser maior do que 2MB.',
            'taxID.required' => 'A taxa de IVA é obrigatória.',
            'taxID.string' => 'A taxa de IVA deve ser um texto.',
            'motivo_isencao.string' => 'O motivo de isenção deve ser um texto.',
            'custo.numeric' => 'O preço de custo deve ser um número.',
            'venda.numeric' => 'O preço de venda deve ser um número.',
            'lucro.numeric' => 'A margem de lucro deve ser um número.',
            'venda_sem_iva.numeric' => 'O preço sem IVA deve ser um número.',
        ];
    }

}
