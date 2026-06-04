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
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($dto): Processo {
            $numero = $dto->numero ?: (string) $this->geradorNumero->gerar($dto->empresaId);

            if ($this->processos->findByNumero($numero) !== null) {
                throw NumeroProcessoDuplicadoException::comNumero($numero);
            }

            $this->rules->assertDataFechoNaoAnterior((string) $dto->dataAbertura, $dto->dataFecho?->__toString());

            // Evita reconversão desnecessária via toArray/fromArray (contrato simétrico do DTO)
            // e garante que o número gerado seja persistido no campo correto.
            $payload = $dto->toArray();
            $payload['NrProcesso'] = $numero;

            $processo = $this->processos->create(CriarProcessoDTO::fromArray($payload));
            $this->criarPastaProcesso->execute($processo);

            return $processo;
        });
    }
}
