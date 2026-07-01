<?php

namespace App\Application\Customer\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;

final readonly class ContaCorrenteMovimentoDTO
{
    public function __construct(
        public int $empresaId,
        public int $customerId,
        public string $tipo,
        public float $valor,
        public ?string $descricao = null,
        public ?string $referencia = null,
        public ?string $observacoes = null,
        public ?CarbonImmutable $dataMovimento = null,
        public ?int $customerAvencaId = null,
        public ?int $processoId = null,
        public ?int $licenciamentoId = null,
        public ?string $origemTipo = null,
        public ?int $origemId = null,
        public ?int $estornadoMovimentoId = null,
        public ?int $createdBy = null,
        public ?array $metadata = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            empresaId: (int) $data['empresa_id'],
            customerId: (int) ($data['customer_id'] ?? $data['cliente_id']),
            tipo: self::normalizeTipo((string) $data['tipo']),
            valor: abs((float) $data['valor']),
            descricao: self::nullableString(Arr::get($data, 'descricao')),
            referencia: self::nullableString(Arr::get($data, 'referencia')),
            observacoes: self::nullableString(Arr::get($data, 'observacoes')),
            dataMovimento: self::date(Arr::get($data, 'data_movimento', Arr::get($data, 'data'))),
            customerAvencaId: self::nullableInt(Arr::get($data, 'customer_avenca_id')),
            processoId: self::nullableInt(Arr::get($data, 'processo_id')),
            licenciamentoId: self::nullableInt(Arr::get($data, 'licenciamento_id')),
            origemTipo: self::nullableString(Arr::get($data, 'origem_tipo')),
            origemId: self::nullableInt(Arr::get($data, 'origem_id')),
            estornadoMovimentoId: self::nullableInt(Arr::get($data, 'estornado_movimento_id')),
            createdBy: self::nullableInt(Arr::get($data, 'created_by')),
            metadata: Arr::get($data, 'metadata'),
        );
    }

    public function toPersistenceArray(): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'customer_id' => $this->customerId,
            'cliente_id' => $this->customerId,
            'customer_avenca_id' => $this->customerAvencaId,
            'processo_id' => $this->processoId,
            'licenciamento_id' => $this->licenciamentoId,
            'origem_tipo' => $this->origemTipo,
            'origem_id' => $this->origemId,
            'tipo' => $this->tipo,
            'descricao' => $this->descricao,
            'valor' => $this->valor,
            'data_movimento' => ($this->dataMovimento ?? now()->toImmutable())->toDateString(),
            'data' => ($this->dataMovimento ?? now()->toImmutable())->toDateString(),
            'referencia' => $this->referencia,
            'observacoes' => $this->observacoes,
            'created_by' => $this->createdBy,
            'estornado_movimento_id' => $this->estornadoMovimentoId,
            'metadata' => $this->metadata,
        ];
    }

    private static function normalizeTipo(string $tipo): string
    {
        return match (mb_strtolower(trim($tipo))) {
            'debito', 'débito', 'factura', 'fatura' => 'debito',
            'credito', 'crédito', 'pagamento' => 'credito',
            'estorno' => 'estorno',
            default => throw new \InvalidArgumentException('Tipo de movimento inválido.'),
        };
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return null;
        }

        return (int) $value;
    }

    private static function date(mixed $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return CarbonImmutable::parse($value);
    }
}
