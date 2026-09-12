<?php

namespace App\Application\FacturacaoIntegracao\DTOs;

use App\Models\ExternalInvoice;

final readonly class EmitirFacturaExternaDTO
{
    /**
     * @param array<int, EmitirFacturaLinhaDTO> $linhas
     */
    public function __construct(
        public ?int $empresaId,
        public int $empresaIntegracaoId,
        public int $customerId,
        public ?int $externalCustomerId = null,
        public string $documentType = ExternalInvoice::DOC_TYPE_FT,
        public int $apiTipo = ExternalInvoice::API_TIPO_FT,
        public int $moeda = 0,
        public string $currency = 'AOA',
        public ?float $cambio = null,
        public ?string $clienteNome = null,
        public ?string $clienteNif = null,
        public ?string $clienteEmail = null,
        public ?string $clienteTelefone = null,
        public ?int $estabelecimentoId = null,
        public ?string $dueDate = null,
        public ?string $localReference = null,
        public ?string $nossaReferencia = null,
        public ?string $vossaReferencia = null,
        public ?string $obs = null,
        public array $dados = [],
        public array $linhas = [],
    ) {
    }
}
