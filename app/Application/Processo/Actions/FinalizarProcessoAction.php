<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Processo\Services\ContaDespachoSequencialService;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class FinalizarProcessoAction
{
    public function __construct(
        private ProcessoRepositoryInterface $processos, 
        private ContaDespachoSequencialService $contaDespachoSequencial,
    ) {
    }


    public function execute(int $id): Processo
    {
        return DB::transaction(function () use ($id): Processo {
            $processo = $this->processos->findOrFail($id);
            $erros = $this->validarRequisitos($processo);

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

    /**
     * @return array<int, string>
     */
    public function validarRequisitos(Processo $processo): array
    {
        $erros = [];

        if (empty($processo->NrDU)) {
            $erros[] = 'O campo NrDU é obrigatório.';
        }

        if (empty($processo->BLC_Porte)) {
            $erros[] = 'O campo BLC_Porte é obrigatório.';
        }

        if (empty($processo->ValorAduaneiro)) {
            $erros[] = 'O campo ValorAduaneiro é obrigatório.';
        }

        if (empty($processo->cif)) {
            $erros[] = 'O campo CIF é obrigatório.';
        }

        if (empty($processo->Cambio)) {
            $erros[] = 'O campo Cambio é obrigatório.';
        }

        if ($processo->mercadorias->isEmpty()) {
            $erros[] = 'Deve haver pelo menos uma mercadoria associada ao processo.';
        }

        if (! $processo->emolumentoTarifa || $processo->emolumentoTarifa->honorario === null || $processo->emolumentoTarifa->honorario < 0) {
            $erros[] = 'Os campos Honorários e Emolumentos Tarifa não podem ser nulos ou negativos.';
        }

        return $erros;
    }
}
