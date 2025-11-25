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
            'imagem_path'        => 'nullable|string|max:200',
            'status'             => 'nullable|integer',
        ];
    }

    public function productData()
    {
        // Se não vier no request → definir 1 automaticamente
        $data['status'] = $data['status'] ?? 1;

        return $data;
    }

    /* public function message()
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
            'ProductGroup.integer' => 'O grupo do produto deve ser um número inteiro.'
        ];
    } */

}
