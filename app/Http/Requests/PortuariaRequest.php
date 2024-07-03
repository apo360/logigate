<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PortuariaRequest extends FormRequest
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
            'Fk_processo' => 'required|integer',
            'ep14' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'terminal' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
        ];
    }
}
