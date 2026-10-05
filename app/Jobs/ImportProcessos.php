<?php
namespace App\Jobs;

use App\Application\Importacao\ImportExecutionContext;
use App\Models\{Empresa, Migracao, User};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ImportProcessos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 1;
    protected ?int $empresaId = null;
    protected ?int $actorId = null;
    public function __construct(protected string $filePath, protected int $importId, int $empresaId, int $actorId)
    {
        $this->empresaId = $empresaId;
        $this->actorId = $actorId;
    }
    public function handle(ImportExecutionContext $context): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($context) {
        $batch = Migracao::query()->lockForUpdate()->findOrFail($this->importId);
        if (in_array($batch->status, ['completed', 'partial'], true)) { return; }
        try {
            abort_unless($this->empresaId && $this->actorId && (int) $batch->empresa_id === $this->empresaId && (int) $batch->actor_id === $this->actorId, 403);
            $actor = User::findOrFail($this->actorId);
            $empresa = Empresa::findOrFail($this->empresaId);
            $context->run($actor, $empresa, \App\Models\Processo::class, function () use ($actor, $empresa, $batch) {
                $import = new \App\Imports\ProcessosImport($empresa, $actor);
                try {
                    Excel::import($import, $this->filePath);
                    $batch->update(['status' => $import->result->status(), 'result' => $import->result->toArray()]);
                } catch (\Throwable $error) {
                    $batch->update(['status' => 'failed', 'result' => $import->result->toArray()]);
                    throw $error;
                }
            });
        } catch (\Throwable $error) {
            $batch->update(['status' => 'failed']);
            report($error);
            throw $error;
        }
        });
    }
    public function failed(\Throwable $error): void
    {
        Migracao::whereKey($this->importId)->whereNotIn('status', ['completed', 'partial'])->update(['status' => 'failed']);
    }
}
