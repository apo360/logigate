<?php

namespace App\Domains\Arquivo\Enums;

enum DocumentoVisibilidadeEnum: string
{
    case PRIVADO = 'privado';
    case CLIENTE = 'cliente';
    case INTERNO = 'interno';
    case PUBLICAVEL = 'publicavel';
}
