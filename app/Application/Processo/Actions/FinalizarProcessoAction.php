<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Processo\Services\ContaDespachoSequencialService;
use App\Domains\Processo\Services\ProcessoFinalizacaoRules;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class FinalizarProcessoAction
{
    public function __construct(
        private ProcessoRepositoryInterface $processos, 
        private ContaDespachoSequencialService $contaDespachoSequencial,
        private ProcessoFinalizacaoRules $finalizacaoRules,
    ) {
    }


    public function execute(int $id): Processo
    {
        return DB::transaction(function () use ($id): Processo {
            $processo = $this->processos->findOrFail($id);
            $erros = $this->finalizacaoRules->validar($processo);

            if ($erros !== []) {
                throw new InvalidArgumentException(implode(' ', $erros));
            }

            $contaDespacho = $this->contaDespachoSequencial->gerarContaDespachoSequencial();

            return $this->processos->update($id, new AtualizarProcessoDTO(
                id: $id,
                dataFecho: now()->toDateString(),
                estado: EstadoProcessoEnum::FINALIZADO,
                contaDespacho: $contaDespacho,
            ));
        });
    }

}
