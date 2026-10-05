<?php
namespace App\Application\Licenciamento\Actions\Import;

use App\Application\Importacao\BatchResult;
use App\Imports\LicenciamentosImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ImportLicenciamentosFromExcelAction
{
    public function execute(UploadedFile $file, int $empresaId, int $userId): BatchResult
    {
        abort_unless(\App\Support\TenantContext::empresaId() === $empresaId && auth()->id() === $userId, 403);
        \Illuminate\Support\Facades\Gate::authorize('create', \App\Models\Licenciamento::class);
        $import = new LicenciamentosImport(\App\Support\TenantContext::empresa(), auth()->user());
        \Illuminate\Support\Facades\DB::transaction(fn () => Excel::import($import, $file));
        return $import->result;
    }
}
