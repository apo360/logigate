<?php

namespace App\Application\Processo\DTOs;

use App\Domains\Licenciamento\ValueObjects\ValorData;
use App\Domains\Licenciamento\ValueObjects\ValorMonetario;
use App\Domains\Processo\Enums\EstadoProcessoEnum;

use Carbon\Carbon;
use Illuminate\Http\Request;

class CriarProcessoDTO
{
    public function __construct(
        public readonly int $customerId,
        public readonly int $userId,
        public readonly int $empresaId,
        public readonly int $exportadorId,
        public readonly int $estanciaId,
        public readonly string $tipoProcesso,
        public readonly ?string $numero,
        public readonly ?string $contaDespacho,
        public readonly ?string $referenciaCliente,
        public readonly ?string $descricao,
        public readonly ValorData $dataAbertura,
        public readonly ?ValorData $dataFecho,
        public readonly EstadoProcessoEnum $estado,
        public readonly ?string $nrDu,
        public readonly ?int $nDar,
        public readonly ?string $marcaFiscal,
        public readonly ?string $blcPorte,
        public readonly ?int $paisOrigem,
        public readonly ?int $paisDestino,
        public readonly ?string $portoOrigem,
        public readonly ?ValorData $dataChegada,        // Agora ValorData
        public readonly ?int $tipoTransporte,
        public readonly ?string $registoTransporte,
        public readonly ?string $nacionalidadeTransporte,
        public readonly ?string $formaPagamento,
        public readonly ?string $codigoBanco,
        public readonly ?string $moeda,
        public readonly ?ValorMonetario $cambio,
        public readonly ?ValorMonetario $valorTotal,
        public readonly ?ValorMonetario $valorAduaneiro,
        public readonly ?ValorMonetario $fobTotal,
        public readonly ?ValorMonetario $frete,
        public readonly ?ValorMonetario $seguro,
        public readonly ?ValorMonetario $cif,
        public readonly ?ValorMonetario $pesoBruto,
        public readonly ?int $portoDesembarqueId,
        public readonly ?int $localizacaoMercadoriaId,
        public readonly ?int $condicaoPagamentoId,
        public readonly ?string $observacoes,
        public readonly ?string $vinheta,
        public readonly ?int $quantidadeBarris,
        public readonly ?ValorData $dataCarregamento,   // Agora ValorData
        public readonly ?ValorMonetario $valorBarrilUSD,
        public readonly ?ValorMonetario $valorBarrilLocal,
        public readonly ?string $numDeslocacoes,
        public readonly ?string $rsmNum,
        public readonly ?string $certificadoOrigem,
        public readonly ?string $guiaExploracao,
    ) {}

    /**
     * Cria o DTO a partir de um array de dados (normalmente validados).
     *
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $data): self
    {
        // Converte/enriquece as datas para ValorData
        $dataAbertura = isset($data['DataAbertura'])
            ? new ValorData($data['DataAbertura'])
            : new ValorData(date('Y-m-d')); // fallback para hoje

        $dataFecho = !empty($data['DataFecho'])
            ? new ValorData($data['DataFecho'])
            : null;

        $dataChegada = !empty($data['DataChegada'])
            ? new ValorData($data['DataChegada'])
            : null;

        $dataCarregamento = !empty($data['data_carregamento'])
            ? new ValorData($data['data_carregamento'])
            : null;

        // Converte valores monetários
        $cambio = isset($data['Cambio']) && $data['Cambio'] !== ''
            ? new ValorMonetario((float) $data['Cambio'])
            : null;

        $valorTotal = isset($data['ValorTotal']) && $data['ValorTotal'] !== ''
            ? new ValorMonetario((float) $data['ValorTotal'])
            : null;

        $valorAduaneiro = isset($data['ValorAduaneiro']) && $data['ValorAduaneiro'] !== ''
            ? new ValorMonetario((float) $data['ValorAduaneiro'])
            : null;

        $fobTotal = isset($data['fob_total']) && $data['fob_total'] !== ''
            ? new ValorMonetario((float) $data['fob_total'])
            : null;

        $frete = isset($data['frete']) && $data['frete'] !== ''
            ? new ValorMonetario((float) $data['frete'])
            : null;

        $seguro = isset($data['seguro']) && $data['seguro'] !== ''
            ? new ValorMonetario((float) $data['seguro'])
            : null;

        $cif = isset($data['cif']) && $data['cif'] !== ''
            ? new ValorMonetario((float) $data['cif'])
            : null;

        $pesoBruto = isset($data['peso_bruto']) && $data['peso_bruto'] !== ''
            ? new ValorMonetario((float) $data['peso_bruto'])
            : null;

        $valorBarrilUSD = isset($data['valor_barril_usd']) && $data['valor_barril_usd'] !== ''
            ? new ValorMonetario((float) $data['valor_barril_usd'])
            : null;

        $valorBarrilLocal = isset($data['valor_barril_local']) && $data['valor_barril_local'] !== ''
            ? new ValorMonetario((float) $data['valor_barril_local'])
            : null;

        // Converte enum
        $estado = isset($data['Estado'])
            ? (is_string($data['Estado']) ? EstadoProcessoEnum::from($data['Estado']) : $data['Estado'])
            : EstadoProcessoEnum::ABERTO;

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
            dataAbertura: $dataAbertura,
            dataFecho: $dataFecho,
            estado: $estado,
            nrDu: $data['NrDU'] ?? null,
            nDar: self::nullableInt($data['N_Dar'] ?? $data['NrDAR'] ?? null),
            marcaFiscal: $data['MarcaFiscal'] ?? null,
            blcPorte: $data['BLC_Porte'] ?? null,
            paisOrigem: self::nullableInt($data['Pais_origem'] ?? null),
            paisDestino: self::nullableInt($data['Pais_destino'] ?? null),
            portoOrigem: $data['PortoOrigem'] ?? null,
            dataChegada: $dataChegada,
            tipoTransporte: self::nullableInt($data['TipoTransporte'] ?? null),
            registoTransporte: $data['registo_transporte'] ?? null,
            nacionalidadeTransporte: $data['nacionalidade_transporte'] ?? null,
            formaPagamento: $data['forma_pagamento'] ?? null,
            codigoBanco: $data['codigo_banco'] ?? null,
            moeda: $data['Moeda'] ?? null,
            cambio: $cambio,
            valorTotal: $valorTotal,
            valorAduaneiro: $valorAduaneiro,
            fobTotal: $fobTotal,
            frete: $frete,
            seguro: $seguro,
            cif: $cif,
            pesoBruto: $pesoBruto,
            portoDesembarqueId: self::nullableInt($data['porto_desembarque_id'] ?? null),
            localizacaoMercadoriaId: self::nullableInt($data['localizacao_mercadoria_id'] ?? null),
            condicaoPagamentoId: self::nullableInt($data['condicao_pagamento_id'] ?? null),
            observacoes: $data['observacoes'] ?? null,
            vinheta: $data['vinheta'] ?? null,
            quantidadeBarris: self::nullableInt($data['quantidade_barris'] ?? null),
            dataCarregamento: $dataCarregamento,
            valorBarrilUSD: $valorBarrilUSD,
            valorBarrilLocal: $valorBarrilLocal,
            numDeslocacoes: $data['num_deslocacoes'] ?? null,
            rsmNum: $data['rsm_num'] ?? null,
            certificadoOrigem: $data['certificado_origem'] ?? null,
            guiaExploracao: $data['guia_exportacao'] ?? $data['guia_exploracao'] ?? null,
        );
    }

    /**
     * Cria o DTO a partir de um Request (após validação).
     */
    public static function fromRequest(Request $request): self
    {
        return self::fromArray($request->validated());
    }

    /**
     * Verifica se a data de fecho é posterior à data de abertura.
     */
    public function isDataFechoAfterAbertura(): bool
    {
        return $this->dataFecho && $this->dataFecho->isAfter($this->dataAbertura);
    }

    /**
     * Verifica se a data de chegada é posterior à data de abertura.
     */
    public function isDataChegadaAfterAbertura(): bool
    {
        return $this->dataChegada && $this->dataChegada->isAfter($this->dataAbertura);
    }

    /**
     * Verifica se a data de carregamento é posterior à data de abertura.
     */
    public function isDataCarregamentoAfterAbertura(): bool
    {
        return $this->dataCarregamento && $this->dataCarregamento->isAfter($this->dataAbertura);
    }

    /**
     * Valida regras de negócio combinadas entre os campos.
     *
     * @throws \DomainException
     */
    public function validateBusinessRules(): void
    {
        // DataFecho não pode ser anterior a DataAbertura
        if ($this->dataFecho && $this->dataFecho->isBefore($this->dataAbertura)) {
            throw new \DomainException('Data de fecho não pode ser anterior à data de abertura.');
        }

        // DataChegada, se informada, deve ser posterior a DataAbertura
        if ($this->dataChegada && $this->dataChegada->isBefore($this->dataAbertura)) {
            throw new \DomainException('Data de chegada não pode ser anterior à data de abertura.');
        }

        // DataCarregamento, se informada, deve ser posterior a DataAbertura
        if ($this->dataCarregamento && $this->dataCarregamento->isBefore($this->dataAbertura)) {
            throw new \DomainException('Data de carregamento não pode ser anterior à data de abertura.');
        }
    }

    /**
     * Converte o DTO para array com os nomes de campo esperados pelo sistema legado.
     */
    public function toArray(): array
    {
        return array_filter([
            'NrProcesso' => $this->numero,
            'ContaDespacho' => $this->contaDespacho,
            'RefCliente' => $this->referenciaCliente,
            'Descricao' => $this->descricao,
            'DataAbertura' => $this->dataAbertura->__toString(),
            'DataFecho' => $this->dataFecho?->__toString(),
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
            'DataChegada' => $this->dataChegada?->__toString(),
            'TipoTransporte' => $this->tipoTransporte,
            'registo_transporte' => $this->registoTransporte,
            'nacionalidade_transporte' => $this->nacionalidadeTransporte,
            'forma_pagamento' => $this->formaPagamento,
            'codigo_banco' => $this->codigoBanco,
            'Moeda' => $this->moeda,
            'Cambio' => $this->cambio?->getValor(),
            'ValorTotal' => $this->valorTotal?->getValor(),
            'ValorAduaneiro' => $this->valorAduaneiro?->getValor(),
            'fob_total' => $this->fobTotal?->getValor(),
            'frete' => $this->frete?->getValor(),
            'seguro' => $this->seguro?->getValor(),
            'cif' => $this->cif?->getValor(),
            'peso_bruto' => $this->pesoBruto?->getValor(),
            'porto_desembarque_id' => $this->portoDesembarqueId,
            'localizacao_mercadoria_id' => $this->localizacaoMercadoriaId,
            'condicao_pagamento_id' => $this->condicaoPagamentoId,
            'observacoes' => $this->observacoes,
            'vinheta' => $this->vinheta,
            'quantidade_barris' => $this->quantidadeBarris,
            'data_carregamento' => $this->dataCarregamento?->__toString(),
            'valor_barril_usd' => $this->valorBarrilUSD?->getValor(),
            'valor_barril_local' => $this->valorBarrilLocal?->getValor(),
            'num_deslocacoes' => $this->numDeslocacoes,
            'rsm_num' => $this->rsmNum,
            'certificado_origem' => $this->certificadoOrigem,
            'guia_exploracao' => $this->guiaExploracao,
        ], static fn ($value): bool => $value !== null);
    }

    private static function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' || $value === 0 || $value === '0' ? null : (int) $value;
    }
}
