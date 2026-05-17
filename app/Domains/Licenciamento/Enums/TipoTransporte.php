<?php

namespace App\Domains\Licenciamento\Enums;

enum TipoTransporte: string
{
    case MARÍTIMO = '1';
    case FERROVIÁRIO = '2';
    case RODOVIÁRIO = '3';
    case AÉREO = '4';
    case CORREIO = '5';
    case MULTIMODAL = '6';
    case INSTALAÇÃO_TRANSPORTE_FIXO = '7';
    case FLUVIAL = '8';

    public function label(): string
    {
        return match ($this) {
            self::MARÍTIMO => 'Marítimo',
            self::AÉREO => 'Aéreo',
            self::FERROVIÁRIO => 'Ferroviário',
            self::RODOVIÁRIO => 'Rodoviário',
            self::CORREIO => 'Correio',
            self::MULTIMODAL => 'Multimodal',
            self::INSTALAÇÃO_TRANSPORTE_FIXO => 'Instalação/Transporte Fixo',
            self::FLUVIAL => 'Fluvial',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::MARÍTIMO => 'Transporte Marítimo',
            self::AÉREO => 'Transporte Aéreo',
            self::FERROVIÁRIO => 'Transporte Ferroviário',
            self::RODOVIÁRIO => 'Transporte Rodoviário',
            self::CORREIO => 'Transporte por Correio',
            self::MULTIMODAL => 'Transporte Multimodal',
            self::INSTALAÇÃO_TRANSPORTE_FIXO => 'Instalação ou Transporte Fixo',
            self::FLUVIAL => 'Transporte Fluvial',
        };
    }
}