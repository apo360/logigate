<?php

namespace App\Application\PautaAduaneira\Actions;

use App\Application\PautaAduaneira\DTOs\PautaAduaneiraData;
use App\Domains\PautaAduaneira\Exceptions\CodigoPautalNaoEncontradoException;
use App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface;
use App\Domains\PautaAduaneira\ValueObjects\CodigoPautal;

final class ConsultarCodigoPautalAction
{
    public function __construct(
        private readonly PautaAduaneiraRepositoryInterface $pautas,
    ) {
    }

    public function execute(string $codigo): PautaAduaneiraData
    {
        $codigoPautal = new CodigoPautal($codigo);
        $pauta = $this->pautas->findByCodigo($codigoPautal->formatted());

        if (! $pauta) {
            throw new CodigoPautalNaoEncontradoException('Código pautal não encontrado.');
        }

        return PautaAduaneiraData::fromModel($pauta);
    }
}
