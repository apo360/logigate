<?php

namespace App\Application\Integracoes\DTOs;

use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;

final readonly class IntegracaoConfigDTO
{
    public function __construct(
        public TipoIntegracaoEnum $tipo,
        public ProvedorIntegracaoEnum $provedor,
        public EstadoIntegracaoEnum $estado,
        public array $config = [],
        public array $credentials = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            tipo: $data['tipo'] instanceof TipoIntegracaoEnum ? $data['tipo'] : TipoIntegracaoEnum::from((string) $data['tipo']),
            provedor: $data['provedor'] instanceof ProvedorIntegracaoEnum ? $data['provedor'] : ProvedorIntegracaoEnum::from((string) $data['provedor']),
            estado: isset($data['estado'])
                ? ($data['estado'] instanceof EstadoIntegracaoEnum ? $data['estado'] : EstadoIntegracaoEnum::from((string) $data['estado']))
                : EstadoIntegracaoEnum::EmConfiguracao,
            config: (array) ($data['config'] ?? []),
            credentials: (array) ($data['credentials'] ?? []),
        );
    }
}
