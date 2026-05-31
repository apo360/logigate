<?php

declare(strict_types=1);

namespace App\Application\Processo\DTOs;

use App\Domains\Processo\Enums\EstadoProcessoEnum;
use Illuminate\Http\Request;

final readonly class CriarProcessoDTO
{
    public function __construct(
        public int $customerId,
        public int $userId,
        public int $empresaId,
        public int $exportadorId,
        public int $estanciaId,
        public string $tipoProcesso,
        public ?string $numero = null,
        public ?string $contaDespacho = null,
        public ?string $referenciaCliente = null,
        public ?string $descricao = null,
        public ?string $dataAbertura = null,
        public ?string $dataFecho = null,
        public EstadoProcessoEnum $estado = EstadoProcessoEnum::ABERTO,
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

    public static function fromRequest(Request $request): self
    {
        return self::fromArray($request->validated());
    }

    public static function fromArray(array $data): self
    {
        $estado = $data['Estado'] ?? EstadoProcessoEnum::ABERTO->value;

        return new self(
            customerId: (int) $data['customer_id'],
            userId: (int) $data['user_id'],
            empresaId: (int) $data['empresa_id'],
            exportadorId: (int) $data['exportador_id'],
            estanciaId: (int) $data['estancia_id'],
            tipoProcesso: (string) $data['TipoProcesso'],
            numero: $data['NrProcesso'] ?? null,
            contaDespacho: $data['ContaDespacho'] ?? null,
            referenciaCliente: $data['RefCliente'] ?? null,
            descricao: $data['Descricao'] ?? null,
            dataAbertura: $data['DataAbertura'] ?? now()->toDateString(),
            dataFecho: $data['DataFecho'] ?? null,
            estado: $estado instanceof EstadoProcessoEnum ? $estado : EstadoProcessoEnum::from((string) $estado),
            nrDu: $data['NrDU'] ?? null,
            nDar: isset($data['N_Dar']) ? (int) $data['N_Dar'] : null,
            marcaFiscal: $data['MarcaFiscal'] ?? null,
            blcPorte: $data['BLC_Porte'] ?? null,
            paisOrigem: isset($data['Pais_origem']) ? (int) $data['Pais_origem'] : null,
            paisDestino: isset($data['Pais_destino']) ? (int) $data['Pais_destino'] : null,
            portoOrigem: $data['PortoOrigem'] ?? null,
            dataChegada: $data['DataChegada'] ?? null,
            tipoTransporte: isset($data['TipoTransporte']) ? (int) $data['TipoTransporte'] : null,
            registoTransporte: $data['registo_transporte'] ?? null,
            nacionalidadeTransporte: $data['nacionalidade_transporte'] ?? null,
            formaPagamento: $data['forma_pagamento'] ?? null,
            codigoBanco: $data['codigo_banco'] ?? null,
            moeda: $data['Moeda'] ?? null,
            cambio: isset($data['Cambio']) ? (float) $data['Cambio'] : null,
            valorTotal: isset($data['ValorTotal']) ? (float) $data['ValorTotal'] : null,
            valorAduaneiro: isset($data['ValorAduaneiro']) ? (float) $data['ValorAduaneiro'] : null,
            fobTotal: isset($data['fob_total']) ? (float) $data['fob_total'] : null,
            frete: isset($data['frete']) ? (float) $data['frete'] : null,
            seguro: isset($data['seguro']) ? (float) $data['seguro'] : null,
            cif: isset($data['cif']) ? (float) $data['cif'] : null,
            pesoBruto: isset($data['peso_bruto']) ? (float) $data['peso_bruto'] : null,
            portoDesembarqueId: isset($data['porto_desembarque_id']) ? (int) $data['porto_desembarque_id'] : null,
            localizacaoMercadoriaId: isset($data['localizacao_mercadoria_id']) ? (int) $data['localizacao_mercadoria_id'] : null,
            condicaoPagamentoId: isset($data['condicao_pagamento_id']) ? (int) $data['condicao_pagamento_id'] : null,
            observacoes: $data['observacoes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'NrProcesso' => $this->numero,
            'ContaDespacho' => $this->contaDespacho,
            'RefCliente' => $this->referenciaCliente,
            'Descricao' => $this->descricao,
            'DataAbertura' => $this->dataAbertura,
            'DataFecho' => $this->dataFecho,
            'TipoProcesso' => $this->tipoProcesso,
            'Estado' => $this->estado->value,
            'customer_id' => $this->customerId,
            'user_id' => $this->userId,
            'empresa_id' => $this->empresaId,
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
}
