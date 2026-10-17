<?php

declare(strict_types=1);

namespace App\Application\Processo\Support;

use App\Models\RegiaoAduaneira;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

final class ExportacaoCrudeFormSupport
{
    public const FIELDS = [
        'quantidade_barris', 'data_carregamento', 'valor_barril_usd',
        'num_deslocacoes', 'rsm_num', 'certificado_origem', 'guia_exportacao',
    ];

    public function options()
    {
        return RegiaoAduaneira::query()->whereIn('abrev', ['EX', 'EXS', 'EXV'])
            ->whereIn('codigo', ['1', '2', '3'])->orderBy('descricao')->get();
    }

    public function rules(): array
    {
        return [
            'TipoProcesso' => ['required', Rule::exists('regiao_aduaneiras', 'id')->where(
                fn (Builder $query) => $query->whereIn('abrev', ['EX', 'EXS', 'EXV'])->whereIn('codigo', ['1', '2', '3'])
            )],
            'quantidade_barris' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'data_carregamento' => ['nullable', 'date', 'after_or_equal:DataAbertura'],
            'valor_barril_usd' => ['nullable', 'numeric', 'min:0'],
            'num_deslocacoes' => ['nullable', 'integer', 'min:0'],
            'rsm_num' => ['nullable', 'string', 'max:100'],
            'certificado_origem' => ['nullable', 'string', 'max:100'],
            'guia_exportacao' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function generalRules(): array
    {
        return array_fill_keys(self::FIELDS, ['prohibited']);
    }

    public static function barrelCount(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantidade_barris' => 'A quantidade de barris deve ser um número inteiro.',
            ]);
        }

        return (int) $value;
    }
}
