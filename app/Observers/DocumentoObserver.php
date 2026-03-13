<?php

namespace App\Observers;

use App\Enums\TipoMovimentoEnum;
use App\Models\ContaCorrente;
use App\Models\SalesInvoice;
use App\Support\ActorContext;
use App\Support\TenantContext;
use Carbon\Carbon;

class DocumentoObserver
{
    public function creating(SalesInvoice $documento): void
    {
        $documento->system_entry_date ??= Carbon::now()->toDateTimeString();
        $documento->empresa_id ??= TenantContext::empresaId();
        $documento->source_id ??= ActorContext::id();
    }

    /**
     * Handle the SalesInvoice "created" event.
     */
    public function created(SalesInvoice $documento): void
    {
        if ($documento->tipo !== 'Factura') {
            return;
        }

        ContaCorrente::create([
            'cliente_id'   => $documento->customer_id,
            'referencia'    => $documento->invoice_number,
            'tipo'          => TipoMovimentoEnum::FACTURA->value,
            'documento_id'  => $documento->id,
            'valor'         => $documento->gross_total,
            'observacoes'   => 'Factura emitida automaticamente',
        ]);
    }

    /**
     * Handle the SalesInvoice "updated" event.
     */
    public function updated(SalesInvoice $salesInvoice): void
    {
        //
    }

    /**
     * Handle the SalesInvoice "deleted" event.
     */
    public function deleted(SalesInvoice $salesInvoice): void
    {
        //
    }

    /**
     * Handle the SalesInvoice "restored" event.
     */
    public function restored(SalesInvoice $salesInvoice): void
    {
        //
    }

    /**
     * Handle the SalesInvoice "force deleted" event.
     */
    public function forceDeleted(SalesInvoice $salesInvoice): void
    {
        //
    }
}
