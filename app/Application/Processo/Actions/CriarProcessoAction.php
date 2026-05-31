<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Domains\Processo\Exceptions\NumeroProcessoDuplicadoException;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Processo\Services\GeradorNumeroProcessoService;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class CriarProcessoAction
{
    public function __construct(
        private ProcessoRepositoryInterface $processos,
        private GeradorNumeroProcessoService $geradorNumero,
    ) {
    }

    public function execute(CriarProcessoDTO $dto): Processo
    {
        return DB::transaction(function () use ($dto): Processo {
            $numero = $dto->numero ?: (string) $this->geradorNumero->gerar($dto->empresaId);

            if ($this->processos->findByNumero($numero) !== null) {
                throw NumeroProcessoDuplicadoException::comNumero($numero);
            }

            if ($dto->dataFecho !== null && $dto->dataAbertura !== null && $dto->dataFecho < $dto->dataAbertura) {
                throw new InvalidArgumentException('A data de fecho não pode ser anterior à data de abertura.');
            }

            return $this->processos->create(CriarProcessoDTO::fromArray($dto->toArray() + [
                'NrProcesso' => $numero,
            ]));
        });
    }
}
