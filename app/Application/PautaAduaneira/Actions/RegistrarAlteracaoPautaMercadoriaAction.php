<?php

namespace App\Application\PautaAduaneira\Actions;

use App\Models\Mercadoria;
use App\Models\MercadoriaPautaAudit;
use App\Models\PautaAduaneira;

final class RegistrarAlteracaoPautaMercadoriaAction
{
    public function execute(
        Mercadoria $mercadoria,
        ?PautaAduaneira $oldPauta,
        PautaAduaneira $newPauta,
        ?string $reason = null,
        string $source = 'system',
        ?int $changedBy = null,
    ): MercadoriaPautaAudit {
        return MercadoriaPautaAudit::create([
            'mercadoria_id' => $mercadoria->id,
            'processo_id' => $mercadoria->Fk_Importacao,
            'licenciamento_id' => $mercadoria->licenciamento_id,
            'old_pauta_aduaneira_id' => $oldPauta?->id,
            'new_pauta_aduaneira_id' => $newPauta->id,
            'old_codigo' => $oldPauta?->codigo ?? $mercadoria->codigo_aduaneiro,
            'new_codigo' => $newPauta->codigo,
            'old_snapshot' => $this->snapshotFromMercadoria($mercadoria),
            'new_snapshot' => $this->snapshotFromPauta($newPauta),
            'changed_by' => $changedBy ?: auth()->id(),
            'reason' => $reason,
            'source' => in_array($source, ['manual', 'ai_suggestion', 'import', 'system'], true) ? $source : 'system',
        ]);
    }

    private function snapshotFromMercadoria(Mercadoria $mercadoria): ?array
    {
        if (! $mercadoria->pauta_aduaneira_id && ! $mercadoria->codigo_aduaneiro) {
            return null;
        }

        return [
            'codigo' => $mercadoria->codigo_pautal_snapshot ?? $mercadoria->codigo_aduaneiro,
            'descricao' => $mercadoria->descricao_pautal_snapshot,
            'rg' => $mercadoria->rg_snapshot,
            'sadc' => $mercadoria->sadc_snapshot,
            'ua' => $mercadoria->ua_snapshot,
            'iva' => $mercadoria->iva_snapshot,
            'ieq' => $mercadoria->ieq_snapshot,
        ];
    }

    private function snapshotFromPauta(PautaAduaneira $pauta): array
    {
        return [
            'codigo' => $pauta->codigo,
            'descricao' => $pauta->descricao,
            'rg' => $pauta->rg,
            'sadc' => $pauta->sadc,
            'ua' => $pauta->ua,
            'iva' => $pauta->iva,
            'ieq' => $pauta->ieq,
        ];
    }
}
