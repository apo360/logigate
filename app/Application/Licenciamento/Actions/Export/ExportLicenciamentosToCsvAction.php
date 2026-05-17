<?php
// app/Application/Licenciamento/Actions/Export/ExportLicenciamentosToCsvAction.php

namespace App\Application\Licenciamento\Actions\Export;

use App\Exports\LicenciamentosExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportLicenciamentosToCsvAction
{
    public function execute(array $licenciamentosIds = [])
    {
        $export = new LicenciamentosExport($licenciamentosIds);
        return Excel::download($export, 'licenciamentos_' . date('Ymd_His') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}