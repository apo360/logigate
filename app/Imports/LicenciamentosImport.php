<?php
// app/Imports/LicenciamentosImport.php

namespace App\Imports;

use App\Models\Licenciamento;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LicenciamentosImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $empresaId;

    public function __construct(int $empresaId)
    {
        $this->empresaId = $empresaId;
    }

    public function model(array $row)
    {
        // Mapeamento das colunas (ajustar conforme cabeçalho do export)
        return new Licenciamento([
            'codigo_licenciamento' => $row['codigo_licenciamento'],
            'cliente_id' => $row['cliente_id'] ?? null, // seria melhor buscar por nome/NIF
            'exportador_id' => $row['exportador_id'] ?? null,
            'estancia_id' => $row['estancia_id'],
            'referencia_cliente' => $row['referencia_cliente'],
            'factura_proforma' => $row['factura_proforma'],
            'descricao' => $row['descricao'],
            'moeda' => $row['moeda'],
            'tipo_declaracao' => $row['tipo_declaracao'] == 'Importação' ? '11' : '21',
            'tipo_transporte' => $row['tipo_transporte'],
            'registo_transporte' => $row['registo_transporte'],
            'nacionalidade_transporte' => $row['nacionalidade_transporte'],
            'manifesto' => $row['manifesto'],
            'data_entrada' => $row['data_entrada'],
            'porto_entrada' => $row['porto_entrada'],
            'peso_bruto' => $row['peso_bruto'],
            'adicoes' => $row['adicoes'],
            'metodo_avaliacao' => $row['metodo_avaliacao'],
            'codigo_volume' => $row['codigo_volume'],
            'qntd_volume' => $row['qntd_volume'],
            'forma_pagamento' => $row['forma_pagamento'],
            'codigo_banco' => $row['codigo_banco'],
            'fob_total' => $row['fob_total'],
            'frete' => $row['frete'],
            'seguro' => $row['seguro'],
            'cif' => $row['cif'],
            'pais_origem' => $row['pais_origem'],
            'porto_origem' => $row['porto_origem'],
            'empresa_id' => $this->empresaId,
        ]);
    }

    public function rules(): array
    {
        return [
            'codigo_licenciamento' => 'required|unique:licenciamentos,codigo_licenciamento',
            'descricao' => 'required',
            'fob_total' => 'required|numeric|min:0',
        ];
    }
}