<?php

namespace App\Imports;

use App\Models\Processo;
use Maatwebsite\Excel\Concerns\ToModel;

class ProcessosImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Processo([
            'NrProcesso' => $row[0],
            'ContaDespacho' => $row[1],
            'RefCliente' => $row[2],
            'Descricao' => $row[3],
            'DataAbertura' => $row[4],
            'DataFecho' => $row[5],
            'TipoProcesso' => $row[6],
            'Situacao' => $row[7],
            'customer_id' => $row[8],
            'exportador_id' => $row[9],
        ]);
    }
}
