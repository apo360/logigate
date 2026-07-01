<?php

namespace App\Application\Customer\Avencas\Actions;

use App\Application\Customer\Actions\RegistrarMovimentoContaCorrenteAction;
use App\Application\Customer\DTOs\ContaCorrenteMovimentoDTO;
use App\Models\ContaCorrente;
use App\Models\CustomerAvenca;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final readonly class GerarMovimentoContaCorrenteDaAvencaAction
{
    public function __construct(
        private RegistrarMovimentoContaCorrenteAction $registrarMovimento,
    ) {
    }

    public function execute(CustomerAvenca $avenca, User $user, int $empresaId, ?CarbonImmutable $dataMovimento = null): ContaCorrente
    {
        $this->assertSchemaReady();
        $this->assertAvencaPodeGerarMovimento($avenca, $empresaId);

        $dataMovimento ??= now()->toImmutable();
        $periodoReferencia = $dataMovimento->format('Y-m');

        $this->assertNaoDuplicado($avenca, $empresaId, $periodoReferencia);

        return DB::transaction(function () use ($avenca, $user, $empresaId, $dataMovimento, $periodoReferencia): ContaCorrente {
            $movimento = $this->registrarMovimento->execute(ContaCorrenteMovimentoDTO::fromArray([
                'empresa_id' => $empresaId,
                'customer_id' => $avenca->customer_id,
                'customer_avenca_id' => $avenca->id,
                'tipo' => 'debito',
                'valor' => (float) $avenca->valor,
                'descricao' => 'Débito de avença: ' . $avenca->titulo_exibicao,
                'data_movimento' => $dataMovimento->toDateString(),
                'origem_tipo' => CustomerAvenca::class,
                'origem_id' => $avenca->id,
                'referencia' => 'AVENCA-' . $avenca->id . '-' . $periodoReferencia,
                'observacoes' => 'Movimento gerado manualmente a partir da avença.',
                'created_by' => $user->id,
                'metadata' => [
                    'gerado_por' => 'customer_avenca',
                    'periodo_referencia' => $periodoReferencia,
                    'avenca_titulo' => $avenca->titulo_exibicao,
                    'periodicidade' => $avenca->periodicidade,
                ],
            ]));

            $this->atualizarMarcadoresDaAvenca($avenca, $movimento, $dataMovimento);

            return $movimento;
        });
    }

    private function assertSchemaReady(): void
    {
        foreach (['empresa_id', 'customer_avenca_id', 'origem_tipo', 'origem_id', 'metadata'] as $column) {
            if (!Schema::hasColumn('conta_correntes', $column)) {
                throw new \RuntimeException("A Conta Corrente ainda não suporta {$column}. Execute a migration evolutiva antes de gerar débitos de avença.");
            }
        }

        foreach (['empresa_id', 'status'] as $column) {
            if (!Schema::hasColumn('customer_avencas', $column)) {
                throw new \RuntimeException("Avenças ainda não suportam {$column}. Execute a migration evolutiva antes de gerar débitos.");
            }
        }
    }

    private function assertAvencaPodeGerarMovimento(CustomerAvenca $avenca, int $empresaId): void
    {
        abort_unless((int) $avenca->empresa_id === $empresaId, 404);
        abort_unless($avenca->estado === 'ativa', 422, 'Apenas avenças ativas podem gerar débito.');
        abort_unless($avenca->esta_ativa, 422, 'A avença não está vigente no período atual.');
        abort_unless((float) $avenca->valor > 0, 422, 'A avença precisa ter valor maior que zero.');
    }

    private function assertNaoDuplicado(CustomerAvenca $avenca, int $empresaId, string $periodoReferencia): void
    {
        $jaExiste = ContaCorrente::query()
            ->where('empresa_id', $empresaId)
            ->where('customer_avenca_id', $avenca->id)
            ->where('tipo', 'debito')
            ->where('metadata->periodo_referencia', $periodoReferencia)
            ->exists();

        abort_if($jaExiste, 422, 'Já existe débito desta avença para o período selecionado.');
    }

    private function atualizarMarcadoresDaAvenca(CustomerAvenca $avenca, ContaCorrente $movimento, CarbonImmutable $dataMovimento): void
    {
        $payload = [
            'ultimo_movimento_id' => $movimento->id,
            'ultima_cobranca_em' => $dataMovimento->toDateString(),
            'proxima_cobranca_em' => $this->proximaCobranca($avenca, $dataMovimento)->toDateString(),
        ];

        $avenca->update(
            collect($payload)
                ->filter(fn (mixed $value, string $column): bool => Schema::hasColumn('customer_avencas', $column))
                ->all()
        );
    }

    private function proximaCobranca(CustomerAvenca $avenca, CarbonImmutable $dataMovimento): CarbonImmutable
    {
        return match ($avenca->periodicidade) {
            'trimestral' => $dataMovimento->addMonthsNoOverflow(3),
            'semestral' => $dataMovimento->addMonthsNoOverflow(6),
            'anual' => $dataMovimento->addYearNoOverflow(),
            default => $dataMovimento->addMonthNoOverflow(),
        };
    }
}
