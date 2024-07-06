<?php

namespace App\Imports;

use App\Models\Exportador;
use Maatwebsite\Excel\Concerns\ToModel;

class ExportadoresImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Exportador([
            'name' => $row[0],
            'country' => $row[1],
            'email' => $row[2],
        ]);
    }
}
