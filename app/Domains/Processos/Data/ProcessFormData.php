<?php

namespace App\Domains\Processos\Data;

final class ProcessFormData
{
    public function __construct(
        public readonly array $attributes,
        public readonly array $mercadorias,
    ) {
    }

    public static function fromArray(array $form): self
    {
        $attributes = $form;

        foreach (['cif', 'ValorAduaneiro', 'fob_total', 'frete', 'seguro', 'Cambio'] as $field) {
            $attributes[$field] = self::normalizeMoney($form[$field] ?? 0);
        }

        foreach ([
            'customer_id',
            'exportador_id',
            'TipoProcesso',
            'estancia_id',
            'TipoTransporte',
            'porto_desembarque_id',
            'localizacao_mercadoria_id',
            'condicao_pagamento_id',
        ] as $field) {
            $attributes[$field] = isset($form[$field]) && $form[$field] !== ''
                ? (int) $form[$field]
                : null;
        }

        return new self(
            attributes: $attributes,
            mercadorias: is_array($form['mercadorias'] ?? null) ? $form['mercadorias'] : [],
        );
    }

    private static function normalizeMoney(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $normalized = str_replace(['.', ','], ['', '.'], (string) $value);

        return round((float) $normalized, 2);
    }
}
