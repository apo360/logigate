<?php

namespace App\Domains\Licenciamento\Services;

use App\Models\Licenciamento;

final readonly class LicenciamentoFaturamentoRules
{
    public function assertMoedaPodeSerAlterada(Licenciamento $licenciamento, string $novaMoeda): void
    {
        if ($licenciamento->moeda === $novaMoeda) {
            return;
        }

        if ($licenciamento->procLicenFaturas()->whereIn('status_fatura', ['emitida', 'paga'])->exists()) {
            throw new \InvalidArgumentException('Não é permitido alterar a moeda pois uma fatura já foi emitida ou paga.');
        }
    }

    public function assertPodeExcluir(Licenciamento $licenciamento): void
    {
        if ($licenciamento->procLicenFaturas()->exists()) {
            throw new \InvalidArgumentException('Não é possível excluir o licenciamento. Existem faturas associadas.');
        }
    }
}
