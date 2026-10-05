<?php
namespace App\Application\Licenciamento\Services;

use App\Models\Licenciamento;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class LicenciamentoProcessLink
{
    public function processId(Licenciamento $license): ?int
    {
        if (! Schema::hasTable('licenciamento_processos')) { return null; }
        $id = DB::table('licenciamento_processos')->where('empresa_id', $license->empresa_id)->where('licenciamento_id', $license->id)->value('processo_id');
        return $id ? (int) $id : null;
    }
    public function attach(Licenciamento $license, Processo $process): void
    {
        abort_unless((int) $license->empresa_id === (int) $process->empresa_id && \App\Support\TenantContext::empresaId() === (int) $license->empresa_id, 403);
        if (! Schema::hasTable('licenciamento_processos')) { throw new \RuntimeException('Actualize o schema dos vínculos antes de converter.'); }
        $existing = $this->processId($license);
        if ($existing && $existing !== (int) $process->id) { throw new \InvalidArgumentException('Licenciamento já associado a outro processo.'); }
        if (! $existing) {
            DB::table('licenciamento_processos')->insert(['empresa_id' => $license->empresa_id, 'licenciamento_id' => $license->id, 'processo_id' => $process->id, 'actor_id' => auth()->id(), 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
