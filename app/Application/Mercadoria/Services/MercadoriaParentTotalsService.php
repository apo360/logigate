<?php

namespace App\Application\Mercadoria\Services;

use App\Models\Licenciamento;
use App\Models\Mercadoria;
use App\Models\Processo;

final class MercadoriaParentTotalsService
{
    public function applyCreate(Mercadoria $mercadoria): void
    {
        $this->applyDelta($mercadoria, (float) $mercadoria->preco_total, (float) $mercadoria->Peso);
    }

    public function applyUpdate(Mercadoria $before, Mercadoria $after): void
    {
        $this->applyDelta(
            $after,
            (float) $after->preco_total - (float) $before->preco_total,
            (float) $after->Peso - (float) $before->Peso
        );
    }

    public function applyDelete(Mercadoria $mercadoria): void
    {
        $this->applyDelta($mercadoria, -((float) $mercadoria->preco_total), -((float) $mercadoria->Peso));
    }

    private function applyDelta(Mercadoria $mercadoria, float $precoDelta, float $pesoDelta): void
    {
        if ($mercadoria->Fk_Importacao) {
            Processo::query()
                ->whereKey($mercadoria->Fk_Importacao)
                ->increment('fob_total', $precoDelta);
        }

        if ($mercadoria->licenciamento_id) {
            Licenciamento::query()
                ->whereKey($mercadoria->licenciamento_id)
                ->increment('fob_total', $precoDelta);

            Licenciamento::query()
                ->whereKey($mercadoria->licenciamento_id)
                ->increment('peso_bruto', $pesoDelta);
        }
    }
}
