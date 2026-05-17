<?php

namespace App\Application\Licenciamento\Actions;

use App\Domains\Licenciamento\Repositories\LicenciamentoRepositoryInterface;
use App\Application\Licenciamento\DTOs\CriarLicenciamentoDTO;
use App\Models\Licenciamento;
use Illuminate\Support\Facades\DB;

class CriarLicenciamentoAction
{
    public function __construct(
        private LicenciamentoRepositoryInterface $repository
    ) {}

    public function execute(CriarLicenciamentoDTO $dto): Licenciamento
    {
        return DB::transaction(function () use ($dto) {
            // Gerar código único de licenciamento (ex: ano + sequência)
            $codigo = $this->gerarCodigoLicenciamento();
            $dto = new CriarLicenciamentoDTO(array_merge($dto->toArray(), [
                'codigo_licenciamento' => $codigo
            ]));

            // Regra: se CIF não foi calculado, calcular a partir de FOB+frete+seguro
            if ($dto->cif->getValor() == 0 && $dto->fob_total->getValor() > 0) {
                $cifValue = $dto->fob_total->getValor() 
                            + $dto->frete->getValor() 
                            + $dto->seguro->getValor();
                $dto = new CriarLicenciamentoDTO(array_merge($dto->toArray(), [
                    'cif' => new \App\Domains\Licenciamento\ValueObjects\ValorMonetario($cifValue)
                ]));
            }

            return $this->repository->create($dto);
        });
    }

    private function gerarCodigoLicenciamento(): string
    {
        $year = now()->format('Y');
        $last = Licenciamento::whereYear('created_at', $year)->max('codigo_licenciamento');
        $sequence = 1;
        if ($last) {
            $parts = explode('/', $last);
            $sequence = (int)end($parts) + 1;
        }
        return sprintf('%s/%04d', $year, $sequence);
    }
}