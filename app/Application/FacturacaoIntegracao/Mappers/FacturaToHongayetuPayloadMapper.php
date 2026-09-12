<?php

namespace App\Application\FacturacaoIntegracao\Mappers;

use App\Application\FacturacaoIntegracao\DTOs\EmitirFacturaExternaDTO;
use App\Application\FacturacaoIntegracao\DTOs\EmitirFacturaLinhaDTO;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturaExternaInvalidaException;
use App\Models\ExternalInvoice;

class FacturaToHongayetuPayloadMapper
{
    public function map(EmitirFacturaExternaDTO $dto, int $externalCustomerId): array
    {
        $this->validate($dto, $externalCustomerId);

        $payload = [
            'tipo' => $dto->apiTipo,
            'moeda' => $dto->moeda,
            'estabelecimento_id' => $dto->estabelecimentoId,
            'artigos' => array_map(fn (EmitirFacturaLinhaDTO $line) => $this->mapLine($line), $dto->linhas),
            'data_expiracao' => $dto->dueDate,
            'obs' => $dto->obs,
            'nossa_referencia' => $dto->nossaReferencia,
            'vossa_referencia' => $dto->vossaReferencia,
            'dados' => $dto->dados,
            'cambio' => $dto->cambio,
        ];

        if ($externalCustomerId > 0) {
            $payload['cliente_id'] = $externalCustomerId;
        } else {
            $payload['cliente_nome'] = $dto->clienteNome;
            $payload['cliente_nif'] = $dto->clienteNif;
            $payload['cliente_email'] = $dto->clienteEmail;
            $payload['cliente_telefone'] = $dto->clienteTelefone;
        }

        return $this->withoutNulls($payload);
    }

    private function validate(EmitirFacturaExternaDTO $dto, int $externalCustomerId): void
    {
        if ($dto->documentType !== ExternalInvoice::DOC_TYPE_FT || $dto->apiTipo !== ExternalInvoice::API_TIPO_FT) {
            throw new FacturaExternaInvalidaException('Nesta fase apenas FT com apiTipo=1 é suportada.');
        }

        if ($dto->linhas === []) {
            throw new FacturaExternaInvalidaException('A factura externa deve conter pelo menos uma linha.');
        }

        if ($externalCustomerId <= 0 && blank($dto->clienteNome)) {
            throw new FacturaExternaInvalidaException('Nome do cliente é obrigatório quando não há cliente_id externo.');
        }

        if ($dto->moeda === 1 && $dto->cambio === null) {
            throw new FacturaExternaInvalidaException('Cambio é obrigatório quando a moeda da API é USD.');
        }

        foreach ($dto->linhas as $line) {
            if (! $line instanceof EmitirFacturaLinhaDTO) {
                throw new FacturaExternaInvalidaException('Todas as linhas devem ser instâncias de EmitirFacturaLinhaDTO.');
            }

            if ($line->externalArtigoId <= 0) {
                throw new FacturaExternaInvalidaException('Cada linha deve conter externalArtigoId válido.');
            }
        }
    }

    private function mapLine(EmitirFacturaLinhaDTO $line): array
    {
        return [
            'artigo_id' => $line->externalArtigoId,
            'quantidade' => $line->quantity,
            'preco' => $line->unitPrice,
            'desconto' => $line->discountAmount,
        ];
    }

    private function withoutNulls(array $payload): array
    {
        return array_filter($payload, fn ($value) => $value !== null);
    }
}
