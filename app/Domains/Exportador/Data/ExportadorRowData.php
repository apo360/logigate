<?php

namespace App\Domains\Exportador\Data;

class ExportadorRowData
{
    public int $id;
    public string $Exportador;
    public ?string $ExportadorTaxID;
    public ?string $Telefone;
    public ?string $Email;
    public ?string $Pais;
    public ?string $Website;

    public function __construct($exportador
    ) {
        $this->id = $exportador->id;
        $this->Exportador = $exportador->Exportador;
        $this->ExportadorTaxID = $exportador->ExportadorTaxID;
        $this->Telefone = $exportador->Telefone;
        $this->Email = $exportador->Email;
        $this->Pais = $exportador->Pais;
        $this->Website = $exportador->Website;
    }
}