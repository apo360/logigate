<?php

namespace App\Observers;

use App\Models\Recibo;
use App\Support\ActorContext;
use Carbon\Carbon;

class ReciboObserver
{
    public function creating(Recibo $recibo): void
    {
        $recibo->data_hora_estado ??= Carbon::now();
        $recibo->estado_pagamento ??= 'N';
        $recibo->sourceID ??= ActorContext::id();
        $recibo->systemID ??= 1;
        $recibo->periodo_contabil ??= now()->format('Y-m');
    }

    public function deleting(Recibo $recibo): void
    {
        $recibo->facturas()->delete();
    }
}
