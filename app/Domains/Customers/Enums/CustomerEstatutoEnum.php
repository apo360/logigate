<?php

namespace App\Domains\Customers\Enums;

enum CustomerEstatutoEnum: string
{
    /**
     * O estatuto do Cliente pode ser Importador, Exportador ou Ambos, dependendo do tipo de operações que o cliente realiza.
     * - Importador: Cliente que adquire mercadorias ou serviços de fornecedores estrangeiros para consumo ou revenda no mercado local.
     * - Exportador: Cliente que vende mercadorias ou serviços para clientes estrangeiros, contribuindo para a geração de receita em moeda estrangeira.
     * - Ambos: Cliente que realiza tanto operações de importação quanto de exportação, podendo atuar em ambos os mercados.
     */

    case IMPORTADOR = 'importador';
    case EXPORTADOR = 'exportador';
    case AMBOS = 'ambos';

    public function label(): string
    {
        return match ($this) {
            self::IMPORTADOR => 'Importador',
            self::EXPORTADOR => 'Exportador',
            self::AMBOS => 'Ambos',
        };
    }
}