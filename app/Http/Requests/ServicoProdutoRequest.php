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
        ];
    }
}
