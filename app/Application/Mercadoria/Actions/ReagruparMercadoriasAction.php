<?php

namespace App\Application\Mercadoria\Actions;

use Illuminate\Support\Facades\DB;

final class ReagruparMercadoriasAction
{
    public function execute(int $licenciamentoId): void
    {
        DB::transaction(function () use ($licenciamentoId): void {
            $parent = app(\App\Application\Mercadoria\Services\MercadoriaTenantAccessService::class)
                ->authorizeLicenciamento(auth()->user(), $licenciamentoId, 'mercadorias.update');
            $items = $parent->mercadorias()->get();
            if ($items->isEmpty()) {
                \App\Models\Licenciamento::query()->whereKey($parent->id)->lockForUpdate()->firstOrFail();
                \App\Models\MercadoriaAgrupada::where('licenciamento_id', $parent->id)->delete();
                $parent->update(['adicoes' => 0]);
            }
            foreach ($items as $item) {
                app(\App\Application\Mercadoria\Services\MercadoriaTenantAccessService::class)
                    ->authorizeMercadoria(auth()->user(), $item, 'licenciamento', $licenciamentoId, 'mercadorias.update');
                app(\App\Application\Mercadoria\Services\MercadoriaAgrupamentoService::class)->addOrUpdate($item);
            }
        }, 3);
    }
}
