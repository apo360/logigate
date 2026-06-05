<?php

namespace App\Application\PautaAduaneira\Actions;

use App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface;
use App\Models\Mercadoria;

final class AssociarPautaMercadoriaAction
{
    public function __construct(
        private readonly PautaAduaneiraRepositoryInterface $pautas,
        private readonly RegistrarAlteracaoPautaMercadoriaAction $registrarAlteracao,
    ) {
    }

    public function execute(
        int $mercadoriaId,
        int $pautaAduaneiraId,
        ?string $reason = null,
        string $source = 'system',
        ?int $changedBy = null,
    ): Mercadoria
    {
        $mercadoria = Mercadoria::findOrFail($mercadoriaId);
        $pauta = $this->pautas->findOrFail($pautaAduaneiraId);
        $oldPauta = $mercadoria->pauta_aduaneira_id ? $this->pautas->find((int) $mercadoria->pauta_aduaneira_id) : null;
        $changed = (int) ($mercadoria->pauta_aduaneira_id ?? 0) !== (int) $pauta->id;

        if ($changed) {
            $this->registrarAlteracao->execute(
                mercadoria: $mercadoria,
                oldPauta: $oldPauta,
                newPauta: $pauta,
                reason: $reason,
                source: $source,
                changedBy: $changedBy,
            );
        }

        $mercadoria->forceFill([
            'pauta_aduaneira_id' => $pauta->id,
            'codigo_aduaneiro' => $pauta->codigo,
            'codigo_pautal_snapshot' => $pauta->codigo,
            'descricao_pautal_snapshot' => $pauta->descricao,
            'rg_snapshot' => $pauta->rg,
            'sadc_snapshot' => $pauta->sadc,
            'ua_snapshot' => $pauta->ua,
            'iva_snapshot' => $pauta->iva,
            'ieq_snapshot' => $pauta->ieq,
            'pauta_snapshot_at' => now(),
        ])->save();

        return $mercadoria->refresh();
    }
}
