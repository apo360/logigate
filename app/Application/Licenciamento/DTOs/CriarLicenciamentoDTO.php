<?php

namespace App\Application\Licenciamento\DTOs;

use App\Application\Licenciamento\Support\LicenciamentoFormSupport;
use App\Domains\Licenciamento\Enums\TipoDeclaracao;
use App\Domains\Licenciamento\Enums\TipoTransporte;
use App\Domains\Licenciamento\Enums\MetodoAvaliacao;
use App\Domains\Licenciamento\ValueObjects\ValorMonetario;
use Illuminate\Http\Request;

class CriarLicenciamentoDTO
{
    public readonly ?int $id;
    public readonly ?string $codigo_licenciamento;
    public readonly int $estancia_id;
    public readonly int $cliente_id;
    public readonly int $exportador_id;
    public readonly int $empresa_id;
    public readonly string $referencia_cliente;
    public readonly string $factura_proforma;
    public readonly string $descricao;
    public readonly string $moeda;
    public readonly TipoDeclaracao $tipo_declaracao;
    public readonly TipoTransporte $tipo_transporte;
    public readonly ?string $registo_transporte;
    public readonly ?int $nacionalidade_transporte;
    public readonly ?string $manifesto;
    public readonly ?string $data_entrada;
    public readonly ?string $porto_entrada;
    public readonly ValorMonetario $peso_bruto;
    public readonly ?int $adicoes;
    public readonly MetodoAvaliacao $metodo_avaliacao;
    public readonly string $codigo_volume;
    public readonly int $qntd_volume;
    public readonly string $forma_pagamento;
    public readonly ?string $codigo_banco;
    public readonly ValorMonetario $fob_total;
    public readonly ValorMonetario $frete;
    public readonly ValorMonetario $seguro;
    public readonly ValorMonetario $cif;
    public readonly ?int $pais_origem;
    public readonly ?string $porto_origem;
    public readonly ?string $txt_gerado;
    public readonly ?string $Nr_factura;
    public readonly ?string $status_fatura;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->codigo_licenciamento = $data['codigo_licenciamento'] ?? null;
        $this->estancia_id = $data['estancia_id'];
        $this->cliente_id = $data['cliente_id'];
        $this->exportador_id = $data['exportador_id'];
        $this->empresa_id = $data['empresa_id'];
        $this->referencia_cliente = $data['referencia_cliente'];
        $this->factura_proforma = $data['factura_proforma'];
        $this->descricao = $data['descricao'];
        $this->moeda = $data['moeda'];
        $this->tipo_declaracao = $data['tipo_declaracao'] instanceof TipoDeclaracao 
            ? $data['tipo_declaracao'] 
            : TipoDeclaracao::from($data['tipo_declaracao']);
        $this->tipo_transporte = $data['tipo_transporte'] instanceof TipoTransporte 
            ? $data['tipo_transporte'] 
            : TipoTransporte::from($data['tipo_transporte']);
        $this->registo_transporte = $data['registo_transporte'] ?? null;
        $this->nacionalidade_transporte = $data['nacionalidade_transporte'] ?? null;
        $this->manifesto = $data['manifesto'] ?? null;
        $this->data_entrada = $data['data_entrada'] ?? null;
        $this->porto_entrada = $data['porto_entrada'] ?? null;
        $this->peso_bruto = $data['peso_bruto'] instanceof ValorMonetario 
            ? $data['peso_bruto'] 
            : new ValorMonetario($data['peso_bruto'] ?? 0);
        $this->adicoes = $data['adicoes'] ?? null;
        $this->metodo_avaliacao = $data['metodo_avaliacao'] instanceof MetodoAvaliacao 
            ? $data['metodo_avaliacao'] 
            : MetodoAvaliacao::from($data['metodo_avaliacao']);
        $this->codigo_volume = $data['codigo_volume'];
        $this->qntd_volume = $data['qntd_volume'];
        $this->forma_pagamento = $data['forma_pagamento'];
        $this->codigo_banco = $data['codigo_banco'] ?? null;
        $this->fob_total = $data['fob_total'] instanceof ValorMonetario 
            ? $data['fob_total'] 
            : new ValorMonetario($data['fob_total'] ?? 0);
        $this->frete = $data['frete'] instanceof ValorMonetario 
            ? $data['frete'] 
            : new ValorMonetario($data['frete'] ?? 0);
        $this->seguro = $data['seguro'] instanceof ValorMonetario 
            ? $data['seguro'] 
            : new ValorMonetario($data['seguro'] ?? 0);
        $this->cif = $data['cif'] instanceof ValorMonetario 
            ? $data['cif'] 
            : new ValorMonetario($data['cif'] ?? 0);
        $this->pais_origem = $data['pais_origem'] ?? null;
        $this->porto_origem = $data['porto_origem'] ?? null;
        $this->txt_gerado = $data['txt_gerado'] ?? null;
        $this->Nr_factura = $data['Nr_factura'] ?? null;
        $this->status_fatura = $data['status_fatura'] ?? null;
    }

    public static function fromRequest(Request $request, int $empresaId): self
    {
        $validated = $request->validate(app(LicenciamentoFormSupport::class)->rules($empresaId));

        // Auto-calculate CIF if not provided
        if (empty($validated['cif']) && isset($validated['fob_total'])) {
            $validated['cif'] = $validated['fob_total'] 
                + ($validated['frete'] ?? 0) 
                + ($validated['seguro'] ?? 0);
        }

        $validated['empresa_id'] = $empresaId;

        return new self($validated);
    }

    public function toArray(): array
    {
        return [
            'codigo_licenciamento' => $this->codigo_licenciamento,
            'estancia_id' => $this->estancia_id,
            'cliente_id' => $this->cliente_id,
            'exportador_id' => $this->exportador_id,
            'empresa_id' => $this->empresa_id,
            'referencia_cliente' => $this->referencia_cliente,
            'factura_proforma' => $this->factura_proforma,
            'descricao' => $this->descricao,
            'moeda' => $this->moeda,
            'tipo_declaracao' => $this->tipo_declaracao->value,
            'tipo_transporte' => $this->tipo_transporte->value,
            'registo_transporte' => $this->registo_transporte,
            'nacionalidade_transporte' => $this->nacionalidade_transporte,
            'manifesto' => $this->manifesto,
            'data_entrada' => $this->data_entrada,
            'porto_entrada' => $this->porto_entrada,
            'peso_bruto' => $this->peso_bruto->getValor(),
            'adicoes' => $this->adicoes,
            'metodo_avaliacao' => $this->metodo_avaliacao->value,
            'codigo_volume' => $this->codigo_volume,
            'qntd_volume' => $this->qntd_volume,
            'forma_pagamento' => $this->forma_pagamento,
            'codigo_banco' => $this->codigo_banco,
            'fob_total' => $this->fob_total->getValor(),
            'frete' => $this->frete->getValor(),
            'seguro' => $this->seguro->getValor(),
            'cif' => $this->cif->getValor(),
            'pais_origem' => $this->pais_origem,
            'porto_origem' => $this->porto_origem,
            'txt_gerado' => $this->txt_gerado,
            'Nr_factura' => $this->Nr_factura,
            'status_fatura' => $this->status_fatura,
        ];
    }
}
