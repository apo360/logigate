<?php
// app/Domains/Licenciamento/Services/LicenciamentoImportExportService.php

namespace App\Domains\Licenciamento\Services;

use App\Application\Licenciamento\Actions\Export\ExportLicenciamentosToCsvAction;
use App\Application\Licenciamento\Actions\Export\ExportLicenciamentosToExcelAction;
use App\Application\Licenciamento\Actions\Import\ImportLicenciamentosFromCsvAction;
use App\Application\Licenciamento\Actions\Import\ImportLicenciamentosFromExcelAction;
use App\Application\Licenciamento\Actions\Import\ImportLicenciamentosFromTxtAction;
use Illuminate\Http\UploadedFile;

class LicenciamentoImportExportService
{
    public function exportToCsv(array $ids = [])
    {
        return app(ExportLicenciamentosToCsvAction::class)->execute($ids);
    }

    public function exportToExcel(array $ids = [])
    {
        return app(ExportLicenciamentosToExcelAction::class)->execute($ids);
    }

    public function import(UploadedFile $file, int $empresaId, int $userId)
    {
        $extension = $file->getClientOriginalExtension();
        switch (strtolower($extension)) {
            case 'csv':
                return app(ImportLicenciamentosFromCsvAction::class)->execute($file, $empresaId);
            case 'xlsx':
            case 'xls':
                return app(ImportLicenciamentosFromExcelAction::class)->execute($file, $empresaId);
            case 'txt':
                return app(ImportLicenciamentosFromTxtAction::class)->execute($file, $empresaId, $userId);
            default:
                throw new \InvalidArgumentException('Formato de arquivo não suportado. Use CSV, XLSX ou TXT.');
        }
    }
}