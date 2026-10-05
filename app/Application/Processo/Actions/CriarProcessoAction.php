<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Arquivo\Actions\CriarPastaProcessoAction;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Domains\Processo\Exceptions\NumeroProcessoDuplicadoException;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Processo\Services\GeradorNumeroProcessoService;
use App\Domains\Processo\Services\ProcessoLifecycleRules;
use App\Models\Processo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final readonly class CriarProcessoAction
{
    public function __construct(
        private ProcessoRepositoryInterface $processos,
        private GeradorNumeroProcessoService $geradorNumero,
        private ProcessoLifecycleRules $rules,
        private CriarPastaProcessoAction $criarPastaProcesso,
    ) {
    }

    public function execute(CriarProcessoDTO $dto): Processo
    {
        abort_unless(\App\Support\TenantContext::empresaId() === $dto->empresaId, 403);
        \Illuminate\Support\Facades\Gate::authorize('create', Processo::class);
        if ($dto->estado === \App\Domains\Processo\Enums\EstadoProcessoEnum::FINALIZADO
            || $dto->contaDespacho !== null || $dto->dataFecho !== null) {
            throw new \InvalidArgumentException('Crie o processo aberto e utilize o comando de finalização.');
        }
        if (! Schema::hasTable('operational_sequences')) {
            throw new \RuntimeException('Actualize o schema das séries antes de criar processos.');
        }
        return DB::transaction(function () use ($dto): Processo {
            $numero = $dto->numero ?: (string) $this->geradorNumero->gerar($dto->empresaId);

            if ($this->processoQuery()->where('empresa_id', $dto->empresaId)->where('NrProcesso', $numero)->exists()) {
                throw NumeroProcessoDuplicadoException::comNumero($numero);
            }

            $this->rules->assertDataFechoNaoAnterior((string) $dto->dataAbertura, $dto->dataFecho?->__toString());

            // Evita reconversão desnecessária via toArray/fromArray (contrato simétrico do DTO)
            // e garante que o número gerado seja persistido no campo correto.
            $payload = $dto->toArray();
            $payload = array_merge($payload, app(\App\Application\Processo\Support\ProcessoFormSupport::class)->calculatedValues(
                $payload['fob_total'] ?? null, $payload['frete'] ?? null, $payload['seguro'] ?? null, $payload['Cambio'] ?? null,
            ));
            $payload['NrProcesso'] = $numero;

            $processo = $this->processos->create(CriarProcessoDTO::fromArray($payload));
            $this->criarPastaProcesso->execute($processo);

            return $processo;
        });
    }

    private function processoQuery()
    {
        $query = Processo::query();

        if (!Schema::hasColumn('processos', 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query;
    }
}
