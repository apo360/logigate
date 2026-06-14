<?php

namespace App\Application\FacturacaoIntegracao\Actions;

use App\Application\FacturacaoIntegracao\Clients\HongayetuFacturacaoClient;
use App\Application\FacturacaoIntegracao\DTOs\FacturaEmitidaDTO;
use App\Application\FacturacaoIntegracao\DTOs\SolicitarFacturaDTO;
use App\Application\Integracoes\Services\IntegracaoResolverService;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Models\Empresa;

final readonly class SolicitarFacturacaoAction
{
    public function __construct(
        private IntegracaoResolverService $resolver,
        private HongayetuFacturacaoClient $client,
    ) {
    }

    public function execute(Empresa $empresa, SolicitarFacturaDTO $data): FacturaEmitidaDTO
    {
        $integracao = $this->resolver->resolve(
            $empresa,
            TipoIntegracaoEnum::Facturacao,
            ProvedorIntegracaoEnum::HongayetuFacturacao,
        );

        return $this->client->emitirFactura($integracao, $data);
    }
}
