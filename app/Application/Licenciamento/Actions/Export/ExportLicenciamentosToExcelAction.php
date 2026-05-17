<?php
// app/Application/Licenciamento/Actions/Export/ExportLicenciamentosToExcelAction.php

namespace App\Application\Licenciamento\Actions\Export;

use App\Exports\LicenciamentosExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportLicenciamentosToExcelAction
{
    public function execute(array $licenciamentosIds = [])
    {
        $export = new LicenciamentosExport($licenciamentosIds);
        return Excel::download($export, 'licenciamentos_' . date('Ymd_His') . '.xlsx');
    }
}