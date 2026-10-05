<?php

namespace App\Application\Licenciamento\Actions;

use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Application\Processo\Support\ProcessoFormSupport;
use App\Application\Licenciamento\Services\LicenciamentoOperationalReadinessService;
use App\Application\Mercadoria\Services\MercadoriaAgrupamentoService;
use App\Models\{Licenciamento, Processo};
use Illuminate\Support\Facades\{Auth, DB, Gate};
use InvalidArgumentException;

class ConstituirProcessoAction
{
    public function __construct(private CriarProcessoAction $criarProcesso)
    {
    }

    public function execute(Licenciamento $licenciamento, ?int $userId = null): Processo
    {
        Gate::authorize('update', $licenciamento);
        Gate::authorize('create', Processo::class);
        abort_unless($userId === null || $userId === Auth::id(), 403);

        return DB::transaction(function () use ($licenciamento): Processo {
            $licenciamento = Licenciamento::query()->whereKey($licenciamento->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $licenciamento);
            if (! \Illuminate\Support\Facades\Schema::hasTable('licenciamento_processos')) {
                throw new \RuntimeException('Actualize o schema dos vínculos antes de converter.');
            }
            $link = app(\App\Application\Licenciamento\Services\LicenciamentoProcessLink::class);
            if ($existingId = $link->processId($licenciamento)) {
                $processo = Processo::query()->where('empresa_id', $licenciamento->empresa_id)->findOrFail($existingId);
                $invalidItems = $licenciamento->mercadorias()->where(function ($query) use ($existingId) {
                    $query->whereNull('Fk_Importacao')->orWhere('Fk_Importacao', '!=', $existingId);
                })->exists();
                if ($invalidItems) { throw new InvalidArgumentException('Mercadorias incompatíveis com o vínculo permanente.'); }
                return $processo;
            }
            $items = $licenciamento->mercadorias()->lockForUpdate()->get();
            $processIds = $items->pluck('Fk_Importacao')->filter()->unique();
            if ($processIds->count() > 1) {
                throw new InvalidArgumentException('As mercadorias estão vinculadas a processos diferentes. Reveja os vínculos antes da conversão.');
            }
            if ($processIds->count() === 1) {
                $processo = Processo::query()->where('empresa_id', $licenciamento->empresa_id)->findOrFail($processIds->first());
                if ($items->contains(fn ($item) => ! $item->Fk_Importacao)) {
                    throw new InvalidArgumentException('Existem mercadorias sem processo num licenciamento já convertido. Reveja a associação.');
                }
                $link->attach($licenciamento, $processo);
                return $processo;
            }
            $readiness = app(LicenciamentoOperationalReadinessService::class)->analyze($licenciamento);
            if (! $readiness['ready_for_process']) {
                throw new InvalidArgumentException(implode(' ', $readiness['process_blockers']));
            }
            $processo = $this->criarProcesso->execute(CriarProcessoDTO::fromArray($this->processData($licenciamento, (int) Auth::id())));
            $link->attach($licenciamento, $processo);
            foreach ($items as $item) {
                $item->Fk_Importacao = $processo->id;
                $item->save();
            }
            if ($items->isNotEmpty()) {
                app(MercadoriaAgrupamentoService::class)->addOrUpdate($items->first());
            }
            return $processo;
        }, 3);
    }

    /** Conversion starts a draft; an exchange rate must subsequently be entered explicitly. */
    public function processData(Licenciamento $licenciamento, int $userId): array
    {
        $values = app(ProcessoFormSupport::class)->calculatedValues($licenciamento->fob_total, $licenciamento->frete, $licenciamento->seguro, null);
        $tipoProcesso = \App\Models\RegiaoAduaneira::query()->where('codigo', $licenciamento->tipo_declaracao)->value('id');
        if (! $tipoProcesso) {
            throw new InvalidArgumentException('O tipo de declaração não tem regime aduaneiro correspondente.');
        }
        return [
            'RefCliente' => $licenciamento->referencia_cliente, 'estancia_id' => $licenciamento->estancia_id,
            'Descricao' => $licenciamento->descricao, 'DataAbertura' => now()->toDateString(),
            'TipoProcesso' => (string) $tipoProcesso, 'Estado' => 'Aberto',
            'customer_id' => $licenciamento->cliente_id, 'user_id' => $userId,
            'empresa_id' => $licenciamento->empresa_id, 'exportador_id' => $licenciamento->exportador_id,
            'forma_pagamento' => $licenciamento->forma_pagamento, 'codigo_banco' => $licenciamento->codigo_banco,
            'fob_total' => $licenciamento->fob_total, 'frete' => $licenciamento->frete, 'seguro' => $licenciamento->seguro,
            'peso_bruto' => $licenciamento->peso_bruto, 'TipoTransporte' => $licenciamento->tipo_transporte,
            'registo_transporte' => $licenciamento->registo_transporte,
            'nacionalidade_transporte' => $licenciamento->nacionalidade_transporte, 'Moeda' => $licenciamento->moeda,
            'Cambio' => null, 'ValorTotal' => $values['cif'],
        ] + $values;
    }
}
