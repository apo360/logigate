<?php

namespace App\Jobs;

use App\Domains\Exportadores\Services\ExportadorImportContext;
use App\Imports\ExportadoresImport;
use App\Models\Empresa;
use App\Models\Migracao;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportExportadores implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $filePath,
        protected int $importId,
        protected int $empresaId,
        protected int $actorId,
    ) {}

    public function handle(ExportadorImportContext $context): void
    {
        $import = Migracao::query()->where('empresa_id', $this->empresaId)->findOrFail($this->importId);
        if ($import->status === 'completed') { return; }
        $empresa = Empresa::findOrFail($this->empresaId);
        $actor = User::findOrFail($this->actorId);
        try {
            $context->run($actor, $empresa, function () use ($empresa, $actor, $import) {
                DB::transaction(function () use ($empresa, $actor, $import) {
                    Excel::import(new ExportadoresImport($empresa, $actor), $this->filePath);
                    $import->update(['status' => 'completed']);
                });
            });
        } catch (Throwable $error) {
            $import->update(['status' => 'failed']);
            Log::error('Erro na importacao de exportadores', ['import_id' => $this->importId, 'empresa_id' => $this->empresaId, 'error' => $error->getMessage()]);
            throw $error;
        }
    }
}
