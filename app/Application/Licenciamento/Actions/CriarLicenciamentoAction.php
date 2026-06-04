<?php

namespace App\Application\Licenciamento\Actions;

use App\Application\Arquivo\Actions\CriarPastaLicenciamentoAction;
use App\Domains\Licenciamento\Repositories\LicenciamentoRepositoryInterface;
use App\Application\Licenciamento\DTOs\CriarLicenciamentoDTO;
use App\Domains\Licenciamento\Services\CalcularCifLicenciamentoService;
use App\Domains\Licenciamento\Services\GeradorCodigoLicenciamentoService;
use App\Models\Licenciamento;
use Illuminate\Support\Facades\DB;

class CriarLicenciamentoAction
{
    public function __construct(
        private LicenciamentoRepositoryInterface $repository,
        private GeradorCodigoLicenciamentoService $geradorCodigo,
        private CalcularCifLicenciamentoService $calcularCif,
        private CriarPastaLicenciamentoAction $criarPastaLicenciamento,
    ) {}

    public function execute(CriarLicenciamentoDTO $dto): Licenciamento
    {
        return DB::transaction(function () use ($dto) {
            $payload = $dto->toArray();
            $payload['codigo_licenciamento'] = $dto->codigo_licenciamento ?: $this->geradorCodigo->gerar($dto->empresa_id);

            if ($dto->cif->getValor() == 0 && $dto->fob_total->getValor() > 0) {
                $payload['cif'] = $this->calcularCif
                    ->calcular($dto->fob_total, $dto->frete, $dto->seguro)
                    ->getValor();
            }

            $licenciamento = $this->repository->create(new CriarLicenciamentoDTO($payload));
            $this->criarPastaLicenciamento->execute($licenciamento);

            return $licenciamento;
        });
    }
}
