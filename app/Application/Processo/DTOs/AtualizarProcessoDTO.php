<?php

declare(strict_types=1);

namespace App\Application\Processo\DTOs;

use App\Domains\Processo\Enums\EstadoProcessoEnum;
use Illuminate\Http\Request;

final readonly class AtualizarProcessoDTO
{
    public function __construct(
        public int $id,
        public ?string $contaDespacho = null,
        public ?string $referenciaCliente = null,
        public ?string $descricao = null,
        public ?string $dataAbertura = null,
        public ?string $dataFecho = null,
        public ?string $tipoProcesso = null,
        public ?EstadoProcessoEnum $estado = null,
        public ?int $customerId = null,
        public ?int $exportadorId = null,
        public ?int $estanciaId = null,
        public ?string $nrDu = null,
        public ?int $nDar = null,
        public ?string $marcaFiscal = null,
        public ?string $blcPorte = null,
        public ?int $paisOrigem = null,
        public ?int $paisDestino = null,
        public ?string $portoOrigem = null,
        public ?string $dataChegada = null,
        public ?int $tipoTransporte = null,
        public ?string $registoTransporte = null,
        public ?string $nacionalidadeTransporte = null,
        public ?string $formaPagamento = null,
        public ?string $codigoBanco = null,
        public ?string $moeda = null,
        public ?float $cambio = null,
        public ?float $valorTotal = null,
        public ?float $valorAduaneiro = null,
        public ?float $fobTotal = null,
        public ?float $frete = null,
        public ?float $seguro = null,
        public ?float $cif = null,
        public ?float $pesoBruto = null,
        public ?int $portoDesembarqueId = null,
        public ?int $localizacaoMercadoriaId = null,
        public ?int $condicaoPagamentoId = null,
        public ?string $observacoes = null,
    ) {
    }

    public static function fromRequest(Request $request, ?int $id = null): self
    {
        return self::fromArray(['id' => $id ?? $request->route('processo')] + $request->validated());
    }

    public static function fromArray(array $data): self
    {
        $estado = self::nullableString($data['Estado'] ?? null);

        return new self(
            id: (int) $data['id'],
            contaDespacho: self::nullableString($data['ContaDespacho'] ?? null),
            referenciaCliente: self::nullableString($data['RefCliente'] ?? null),
            descricao: self::nullableString($data['Descricao'] ?? null),
            dataAbertura: self::nullableString($data['DataAbertura'] ?? null),
            dataFecho: self::nullableString($data['DataFecho'] ?? null),
            tipoProcesso: self::nullableString($data['TipoProcesso'] ?? null),
            estado: $estado === null ? null : ($estado instanceof EstadoProcessoEnum ? $estado : EstadoProcessoEnum::from((string) $estado)),
            customerId: self::nullableInt($data['customer_id'] ?? null),
            exportadorId: self::nullableInt($data['exportador_id'] ?? null),
            estanciaId: self::nullableInt($data['estancia_id'] ?? null),
            nrDu: self::nullableString($data['NrDU'] ?? null),
            nDar: self::nullableInt($data['N_Dar'] ?? $data['NrDAR'] ?? null),
            marcaFiscal: self::nullableString($data['MarcaFiscal'] ?? $data['NrMarcaFiscal'] ?? null),
            blcPorte: self::nullableString($data['BLC_Porte'] ?? null),
            paisOrigem: self::nullableInt($data['Pais_origem'] ?? null),
            paisDestino: self::nullableInt($data['Pais_destino'] ?? null),
            portoOrigem: self::nullableString($data['PortoOrigem'] ?? null),
            dataChegada: self::nullableString($data['DataChegada'] ?? null),
            tipoTransporte: self::nullableInt($data['TipoTransporte'] ?? null),
            registoTransporte: self::nullableString($data['registo_transporte'] ?? null),
            nacionalidadeTransporte: self::nullableString($data['nacionalidade_transporte'] ?? null),
            formaPagamento: self::nullableString($data['forma_pagamento'] ?? null),
            codigoBanco: self::nullableString($data['codigo_banco'] ?? null),
            moeda: self::nullableString($data['Moeda'] ?? null),
            cambio: self::nullableFloat($data['Cambio'] ?? null),
            valorTotal: self::nullableFloat($data['ValorTotal'] ?? null),
            valorAduaneiro: self::nullableFloat($data['ValorAduaneiro'] ?? null),
            fobTotal: self::nullableFloat($data['fob_total'] ?? null),
            frete: self::nullableFloat($data['frete'] ?? null),
            seguro: self::nullableFloat($data['seguro'] ?? null),
            cif: self::nullableFloat($data['cif'] ?? null),
            pesoBruto: self::nullableFloat($data['peso_bruto'] ?? null),
            portoDesembarqueId: self::nullableInt($data['porto_desembarque_id'] ?? null),
            localizacaoMercadoriaId: self::nullableInt($data['localizacao_mercadoria_id'] ?? null),
            condicaoPagamentoId: self::nullableInt($data['condicao_pagamento_id'] ?? null),
            observacoes: self::nullableString($data['observacoes'] ?? null),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'ContaDespacho' => $this->contaDespacho,
            'RefCliente' => $this->referenciaCliente,
            'Descricao' => $this->descricao,
            'DataAbertura' => $this->dataAbertura,
            'DataFecho' => $this->dataFecho,
            'TipoProcesso' => $this->tipoProcesso,
            'Estado' => $this->estado?->value,
            'customer_id' => $this->customerId,
            'exportador_id' => $this->exportadorId,
            'estancia_id' => $this->estanciaId,
            'NrDU' => $this->nrDu,
            'N_Dar' => $this->nDar,
            'MarcaFiscal' => $this->marcaFiscal,
            'BLC_Porte' => $this->blcPorte,
            'Pais_origem' => $this->paisOrigem,
            'Pais_destino' => $this->paisDestino,
            'PortoOrigem' => $this->portoOrigem,
            'DataChegada' => $this->dataChegada,
            'TipoTransporte' => $this->tipoTransporte,
            'registo_transporte' => $this->registoTransporte,
            'nacionalidade_transporte' => $this->nacionalidadeTransporte,
            'forma_pagamento' => $this->formaPagamento,
            'codigo_banco' => $this->codigoBanco,
            'Moeda' => $this->moeda,
            'Cambio' => $this->cambio,
            'ValorTotal' => $this->valorTotal,
            'ValorAduaneiro' => $this->valorAduaneiro,
            'fob_total' => $this->fobTotal,
            'frete' => $this->frete,
            'seguro' => $this->seguro,
            'cif' => $this->cif,
            'peso_bruto' => $this->pesoBruto,
            'porto_desembarque_id' => $this->portoDesembarqueId,
            'localizacao_mercadoria_id' => $this->localizacaoMercadoriaId,
            'condicao_pagamento_id' => $this->condicaoPagamentoId,
            'observacoes' => $this->observacoes,
        ], static fn ($value): bool => $value !== null);
    }

    private static function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' || $value === 0 || $value === '0' ? null : (int) $value;
    }

    private static function nullableFloat(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }

    private static function nullableString(mixed $value): ?string
    {
        return $value === null || $value === '' ? null : (string) $value;
    }
}
