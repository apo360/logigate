<?php

namespace App\Domains\Licenciamento\Enums;

enum MetodoAvaliacao: string
{
    case GATT = 'GATT';
    case OUTRO = 'Outro';

    public function label(): string
    {
        return match ($this) {
            self::GATT => 'GATT',
            self::OUTRO => 'Outro',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            
            self::GATT => 'Avaliação com base no método GATT',
            self::OUTRO => 'Avaliação com base em outro método',};
    }
}