<?php

namespace App\Domains\FacturacaoIntegracao\DTOs;

use App\Domains\FacturacaoIntegracao\Enums\MetodoPagamentoHongayetuEnum;
use InvalidArgumentException;

class MetodoPagamentoFacturaDTO
{
    public function __construct(
        public readonly MetodoPagamentoHongayetuEnum $tipo,
        public readonly float $valor,
        public readonly ?int $bancoId = null,
        public readonly ?string $referencia = null,
    ) {}

    /**
     * Converte o DTO para o formato esperado pela API Hongayetu.
     * (os campos empresaId e empresaIntegracaoId não são enviados à API,
     *  são apenas para uso interno)
     */
    public function toArray(): array
    {
        return [
            'tipo'       => $this->tipo->value,
            'valor'      => $this->valor,
            'banco_id'   => $this->bancoId,
            'referencia' => $this->referencia,
        ];
    }

    /**
     * Cria uma instância a partir de um array (ex: request).
     */
    public static function fromArray(array $data): self
    {
        $tipo = MetodoPagamentoHongayetuEnum::from($data['tipo']);

        return new self(
            tipo: $tipo,
            valor: (float) $data['valor'],
            bancoId: isset($data['banco_id']) ? (int) $data['banco_id'] : null,
            referencia: $data['referencia'] ?? null,
        );
    }

    /**
     * Valida as regras de negócio.
     * (A validação do banco depende de uma consulta externa, por isso não a incluímos aqui.)
     */
    public function assertValid(): void
    {
        if ($this->tipo->requiresBanco() && $this->bancoId === null) {
            throw new InvalidArgumentException(
                "O método '{$this->tipo->label()}' exige a seleção de um banco."
            );
        }

        // Validação do valor do pagamento
        if ($this->valor <= 0) {
            throw new InvalidArgumentException(
                'O valor do pagamento deve ser superior a zero.'
            );
        }

        // Validação da referência
        if (!$this->tipo->acceptsReferencia() && $this->referencia !== null) {
            throw new InvalidArgumentException(
                "O método '{$this->tipo->label()}' não aceita referência."
            );
        }
    }
}