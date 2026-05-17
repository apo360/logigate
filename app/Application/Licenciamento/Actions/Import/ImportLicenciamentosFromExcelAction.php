<?php
// app/Application/Licenciamento/Actions/Import/ImportLicenciamentosFromExcelAction.php

namespace App\Application\Licenciamento\Actions\Import;

use App\Imports\LicenciamentosImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

class ImportLicenciamentosFromExcelAction
{
    public function execute(UploadedFile $file, int $empresaId): void
    {
        $import = new LicenciamentosImport($empresaId);
        Excel::import($import, $file);
    }
}