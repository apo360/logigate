<?php

namespace App\Domains\Integracoes\Enums;

enum EstadoIntegracaoEnum: string
{
    public const ACTIVO = self::Activo;
    public const INACTIVO = self::Inactivo;
    public const ERRO = self::Erro;
    public const EM_CONFIGURACAO = self::EmConfiguracao;

    case EmConfiguracao = 'em_configuracao';
    case Activo = 'activo';
    case Inactivo = 'inactivo';
    case Erro = 'erro';

    /**
     * Backward-compatible alias for older internal code.
     */
    public const Rascunho = self::EmConfiguracao;

    public function label(): string
    {
        return match ($this) {
            self::EmConfiguracao => 'Em configuração',
            self::Activo => 'Activo',
            self::Inactivo => 'Inactivo',
            self::Erro => 'Erro',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EmConfiguracao => 'slate',
            self::Activo => 'green',
            self::Inactivo => 'amber',
            self::Erro => 'red',
        };
    }
}
