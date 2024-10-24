<?php

namespace App\Imports;

use App\Models\PautaAduaneira;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PautaAduaneiraImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }

    public function model(array $row)
    {
        return new PautaAduaneira([
            'codigo' => $row['codigo'],
            'descricao' => $row['descricao'],
            'uq' => $row['uq'] ?? 'nulo',
            'rg' => $row['rg'] ?? 0.00,
            'sadc' => $row['sadc'] ?? 0.00,
            'ua' => $row['ua'] ?? 0.00,
            'requisitos' => $row['requisitos'],
            'observacao' => $row['observacao'],
        ]);
    }

    public function chunkSize(): int
    {
        return 1000; // Define o tamanho do chunk
    }

}
