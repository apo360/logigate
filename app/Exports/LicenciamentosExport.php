<?php

namespace App\Exports;

use App\Models\Licenciamento;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;

class LicenciamentosExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return Licenciamento::where('empresa_id', Auth::user()->empresas->first()->id)->get(); // Retorna todos os licenciamentos
    }

    public function headings(): array
    {
        return [
            'Cliente', 'Descrição', 'Peso Bruto', 'Unidade', 'Origem', 'Estado', 'CIF', 'Moeda', 'Factura'
        ];
    }

    public function styles($sheet)
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true); // Deixa os cabeçalhos em negrito
    }
}

