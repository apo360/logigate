<?php

namespace App\Application\Customer\Actions;

use App\Application\Customer\DTOs\ContaCorrenteMovimentoDTO;
use App\Models\ContaCorrente;

final readonly class EstornarMovimentoContaCorrenteAction
{
    public function __construct(
        private RegistrarMovimentoContaCorrenteAction $registrarMovimento,
    ) {
    }

    public function execute(ContaCorrente $movimento, int $empresaId, int $userId, ?string $motivo = null): ContaCorrente
    {
        $tipoEstorno = $movimento->tipo === 'debito' ? 'credito' : 'debito';

        return $this->registrarMovimento->execute(new ContaCorrenteMovimentoDTO(
            empresaId: $empresaId,
            customerId: (int) $movimento->cliente_id,
            tipo: $tipoEstorno,
            valor: abs((float) $movimento->valor),
            descricao: 'Estorno: ' . ($movimento->descricao ?: 'movimento de conta corrente'),
            referencia: $movimento->referencia,
            observacoes: $motivo,
            dataMovimento: now()->toImmutable(),
            origemTipo: 'conta_corrente',
            origemId: (int) $movimento->id,
            estornadoMovimentoId: (int) $movimento->id,
            createdBy: $userId,
            metadata: [
                'estorno_de_movimento_id' => $movimento->id,
            ],
        ));
    }
}
