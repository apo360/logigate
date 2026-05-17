<?php
// app/Exports/LicenciamentosExport.php

namespace App\Exports;

use App\Models\Licenciamento;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LicenciamentosExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $licenciamentosIds;

    public function __construct(array $licenciamentosIds = [])
    {
        $this->licenciamentosIds = $licenciamentosIds;
    }

    public function query()
    {
        $query = Licenciamento::with(['cliente', 'exportador', 'estancia']);
        if (!empty($this->licenciamentosIds)) {
            $query->whereIn('id', $this->licenciamentosIds);
        }
        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', 'Código Licenciamento', 'Cliente', 'NIF Cliente', 'Exportador', 'Estância',
            'Referência Cliente', 'Factura Proforma', 'Descrição', 'Moeda', 'Tipo Declaração',
            'Tipo Transporte', 'Registo Transporte', 'Nacionalidade', 'Manifesto', 'Data Entrada',
            'Porto Entrada', 'Peso Bruto', 'Adições', 'Método Avaliação', 'Código Volume',
            'Qtd Volume', 'Forma Pagamento', 'Código Banco', 'FOB Total', 'Frete', 'Seguro', 'CIF',
            'País Origem', 'Porto Origem', 'Status', 'Data Criação'
        ];
    }

    public function map($licenciamento): array
    {
        return [
            $licenciamento->id,
            $licenciamento->codigo_licenciamento,
            $licenciamento->cliente->CompanyName ?? '',
            $licenciamento->cliente->CustomerTaxID ?? '',
            $licenciamento->exportador->Exportador ?? '',
            $licenciamento->estancia->desc_estancia ?? '',
            $licenciamento->referencia_cliente,
            $licenciamento->factura_proforma,
            $licenciamento->descricao,
            $licenciamento->moeda,
            $licenciamento->tipo_declaracao == '11' ? 'Importação' : 'Exportação',
            $licenciamento->tipo_transporte,
            $licenciamento->registo_transporte,
            $licenciamento->pais->pais ?? '',
            $licenciamento->manifesto,
            $licenciamento->data_entrada,
            $licenciamento->porto_entrada,
            $licenciamento->peso_bruto,
            $licenciamento->adicoes,
            $licenciamento->metodo_avaliacao,
            $licenciamento->codigo_volume,
            $licenciamento->qntd_volume,
            $licenciamento->forma_pagamento,
            $licenciamento->codigo_banco,
            $licenciamento->fob_total,
            $licenciamento->frete,
            $licenciamento->seguro,
            $licenciamento->cif,
            $licenciamento->paisOrigem->pais ?? '',
            $licenciamento->porto_origem,
            $licenciamento->estado_licenciamento,
            $licenciamento->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}