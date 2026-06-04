<?php

namespace App\Application\Mercadoria\Services;

use App\Models\Mercadoria;
use App\Models\MercadoriaAgrupada;

final class MercadoriaAgrupamentoService
{
    public function addOrUpdate(Mercadoria $mercadoria): void
    {
        MercadoriaAgrupada::storeAndUpdateAgrupamento($mercadoria);
    }

    public function remove(Mercadoria $mercadoria): void
    {
        MercadoriaAgrupada::removeFromAgrupamento($mercadoria);
    }
}
