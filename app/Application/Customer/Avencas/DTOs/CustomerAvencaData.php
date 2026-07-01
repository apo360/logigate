<?php

namespace App\Application\Customer\Avencas\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;

final readonly class CustomerAvencaData
{
    public const STATUSES = ['rascunho', 'ativa', 'suspensa', 'cancelada', 'encerrada', 'expirada'];

    public const PERIODICIDADES = ['mensal', 'trimestral', 'semestral', 'anual'];

    public function __construct(
        public int $empresaId,
        public int $customerId,
        public string $titulo,
        public float $valor,
        public string $periodicidade,
        public CarbonImmutable $dataInicio,
        public ?CarbonImmutable $dataFim = null,
        public ?string $descricao = null,
        public ?int $diaCobranca = null,
        public string $status = 'rascunho',
        public ?string $observacoes = null,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
        public ?array $metadata = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            empresaId: (int) $data['empresa_id'],
            customerId: (int) ($data['customer_id'] ?? $data['cliente_id']),
            titulo: self::nullableString(Arr::get($data, 'titulo')) ?: 'Avença de Cliente',
            valor: (float) Arr::get($data, 'valor', 0),
            periodicidade: self::normalizePeriodicidade((string) Arr::get($data, 'periodicidade', 'mensal')),
            dataInicio: CarbonImmutable::parse(Arr::get($data, 'data_inicio')),
            dataFim: self::date(Arr::get($data, 'data_fim')),
            descricao: self::nullableString(Arr::get($data, 'descricao')),
            diaCobranca: self::nullableInt(Arr::get($data, 'dia_cobranca')),
            status: self::normalizeStatus((string) Arr::get($data, 'status', 'rascunho')),
            observacoes: self::nullableString(Arr::get($data, 'observacoes')),
            createdBy: self::nullableInt(Arr::get($data, 'created_by')),
            updatedBy: self::nullableInt(Arr::get($data, 'updated_by')),
            metadata: Arr::get($data, 'metadata'),
        );
    }

    public function toArray(): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'customer_id' => $this->customerId,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'valor' => $this->valor,
            'periodicidade' => $this->periodicidade,
            'data_inicio' => $this->dataInicio->toDateString(),
            'data_fim' => $this->dataFim?->toDateString(),
            'dia_cobranca' => $this->diaCobranca,
            'status' => $this->status,
            'ativo' => $this->status === 'ativa',
            'observacoes' => $this->observacoes,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'metadata' => $this->metadata,
        ];
    }

    public static function normalizeStatus(string $status): string
    {
        $status = mb_strtolower(trim($status));

        return in_array($status, self::STATUSES, true) ? $status : 'rascunho';
    }

    private static function normalizePeriodicidade(string $periodicidade): string
    {
        $periodicidade = mb_strtolower(trim($periodicidade));

        return in_array($periodicidade, self::PERIODICIDADES, true) ? $periodicidade : 'mensal';
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
