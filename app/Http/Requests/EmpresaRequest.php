<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpresaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //Regras Gerais
            'CodFactura' => 'nullable|string|max:150',
            'CodProcesso' => 'nullable|string|max:150',
            'ActividadeComercial' => 'nullable|string|max:150',
            'Designacao' => 'nullable|string|max:150',
            'NIF' => 'required|NIF|unique:empresas,NIF,' . $this->id,
            'Cedula' => 'required|Cedula|unique:empresas,Cedula,' . $this->id,
            'Logotipo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'Slogan' => 'nullable|string|max:150',
            'Endereco_completo' => 'nullable|string|max:150',
            'Provincia' => 'nullable|string|max:150',
            'Cidade' => 'nullable|string|max:150',
            'Dominio' => 'nullable|string|max:150',
            'Email' => 'nullable|string|max:150',
            'Fax' => 'nullable|string|max:150',
            'Contacto_movel' => 'nullable|string|max:150',
            'Contacto_fixo' => 'nullable|string|max:150',
            'Sigla' => 'nullable|string|max:150',
            'nome' => 'nullable|string|max:150',
            'apelido' => 'nullable|string|max:150',
            'telefone' => 'nullable|string|max:150',
            'tipo' => 'nullable|string|max:150',
            'user_id' => 'nullable|exists:users,id',
        ];
    }
}
