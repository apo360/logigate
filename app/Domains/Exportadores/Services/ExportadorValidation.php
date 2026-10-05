<?php

namespace App\Domains\Exportadores\Services;

use Illuminate\Support\Facades\Validator;

final class ExportadorValidation
{
    public static function rules(): array
    {
        return [
            'Exportador' => 'required|string|max:100',
            'ExportadorTaxID' => 'nullable|string|min:6|max:20',
            'AccountID' => 'nullable|string|max:30',
            'Endereco' => 'nullable|string|max:254',
            'Telefone' => 'nullable|string|max:20',
            'Email' => 'nullable|email|max:254',
            'Pais' => 'required|integer|exists:paises,id',
            'Website' => 'nullable|url|max:60',
            'Cidade' => 'nullable|string|max:60',
            'codigo_exportador' => 'nullable|string|max:150',
            'additional_info' => 'nullable|string|max:255',
            'status' => 'nullable|in:ATIVO,INATIVO',
        ];
    }

    public static function validate(array $data): array
    {
        return Validator::make($data, self::rules())->validate();
    }
}
