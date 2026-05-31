<?php

declare(strict_types=1);

namespace App\Domains\Processo\Enums;

enum EstadoProcessoEnum: string
{
    case ABERTO = 'Aberto';
    case EM_ANALISE = 'Em analise';
    case AGUARDANDO_DOCUMENTOS = 'Aguardando documentos';
    case EM_DESPACHO = 'Em despacho';
    case FINALIZADO = 'Finalizado';
    case CANCELADO = 'Cancelado';

    public function label(): string
    {
        return match ($this) {
            self::ABERTO => 'Aberto',
            self::EM_ANALISE => 'Em análise',
            self::AGUARDANDO_DOCUMENTOS => 'Aguardando documentos',
            self::EM_DESPACHO => 'Em despacho',
            self::FINALIZADO => 'Finalizado',
            self::CANCELADO => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ABERTO => 'gray',
            self::EM_ANALISE => 'blue',
            self::AGUARDANDO_DOCUMENTOS => 'yellow',
            self::EM_DESPACHO => 'indigo',
            self::FINALIZADO => 'green',
            self::CANCELADO => 'red',
        };
    }

    public function isFinalizado(): bool
    {
        return $this === self::FINALIZADO;
    }

    public function isCancelado(): bool
    {
        return $this === self::CANCELADO;
    }
}
