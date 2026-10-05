<?php

namespace App\Application\Mercadoria\Services;

use App\Models\{Licenciamento, Mercadoria, MercadoriaAgrupada, Processo};
use Illuminate\Support\Facades\DB;
use App\Domains\Processo\Services\ProcessoLifecycleRules;

final class MercadoriaAgrupamentoService
{
    public function addOrUpdate(Mercadoria $mercadoria): void
    {
        $this->synchronize($mercadoria);
    }

    public function remove(Mercadoria $mercadoria): void
    {
        $this->synchronize($mercadoria, (int) $mercadoria->id);
    }

    public function synchronize(Mercadoria $mercadoria, ?int $excludeId = null): void
    {
        DB::transaction(function () use ($mercadoria, $excludeId): void {
            foreach ([['processo_id', 'Fk_Importacao', Processo::class], ['licenciamento_id', 'licenciamento_id', Licenciamento::class]] as [$groupKey, $itemKey, $parentClass]) {
                $parentId = $mercadoria->getAttribute($itemKey);
                if (! $parentId) {
                    continue;
                }
                $parent = $parentClass::query()->whereKey($parentId)->lockForUpdate()->firstOrFail();
                if ($parent instanceof Processo) {
                    app(ProcessoLifecycleRules::class)->assertMercadoriasEditaveis($parent);
                }
                $items = Mercadoria::query()->where($itemKey, $parentId)
                    ->when($excludeId !== null, fn ($query) => $query->whereKeyNot($excludeId))
                    ->orderBy('id')->get();
                // One group per document/code. Shared items appear in both documents.
                MercadoriaAgrupada::query()->where($groupKey, $parentId)->delete();
                foreach ($items->groupBy('codigo_aduaneiro') as $code => $members) {
                    MercadoriaAgrupada::create([
                        'codigo_aduaneiro' => $code,
                        'processo_id' => $groupKey === 'processo_id' ? $parentId : null,
                        'licenciamento_id' => $groupKey === 'licenciamento_id' ? $parentId : null,
                        'quantidade_total' => $members->sum('Quantidade'),
                        'peso_total' => $members->sum('Peso'),
                        'preco_total' => $members->sum('preco_total'),
                        'mercadorias_ids' => json_encode($members->modelKeys(), JSON_THROW_ON_ERROR),
                    ]);
                }
                if ($parent instanceof Licenciamento) {
                    $parent->adicoes = $items->pluck('codigo_aduaneiro')->unique()->count();
                    $parent->save();
                }
            }
        }, 3);
    }
}
