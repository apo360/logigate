<?php

declare(strict_types=1);

namespace App\Application\Processo\Queries;

use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Models\Processo;
use Illuminate\Support\Collection;

final readonly class ListarProcessosFinalizaveisQuery
{
    public function execute(int $empresaId): Collection
    {
        return Processo::query()
            ->whereNotNull('NrDU')
            ->whereNotNull('BLC_Porte')
            ->whereNotNull('ValorAduaneiro')
            ->whereNotNull('cif')
            ->whereNotNull('Cambio')
            ->whereHas('mercadorias')
            ->whereHas('emolumentoTarifa', function ($query): void {
                $query->whereNotNull('honorario')->where('honorario', '>=', 0);
            })
            ->where('Estado', '!=', EstadoProcessoEnum::FINALIZADO->value)
            ->where('empresa_id', $empresaId)
            ->get();
    }
}
