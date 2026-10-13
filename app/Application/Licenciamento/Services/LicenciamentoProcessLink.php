<?php

namespace App\Application\Licenciamento\Services;

use App\Models\Licenciamento;
use App\Models\Processo;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class LicenciamentoProcessLink
{
    /** NULL means that the canonical association schema is unavailable. */
    public function analyticsExpression(string $alias): string
    {
        if ($alias !== 'r') {
            throw new \InvalidArgumentException('Internal analytics alias required.');
        }
        if (! Schema::hasTable('licenciamento_processos')) {
            return 'NULL';
        }

        return 'EXISTS (SELECT 1 FROM licenciamento_processos lp JOIN processos p ON p.id=lp.processo_id AND p.empresa_id=lp.empresa_id AND p.deleted_at IS NULL WHERE lp.licenciamento_id=r.id AND lp.empresa_id=r.empresa_id)';
    }

    public function processId(Licenciamento $license): ?int
    {
        if (! Schema::hasTable('licenciamento_processos')) {
            return null;
        }
        $id = DB::table('licenciamento_processos')->where('empresa_id', $license->empresa_id)->where('licenciamento_id', $license->id)->value('processo_id');

        return $id ? (int) $id : null;
    }

    public function attach(Licenciamento $license, Processo $process): void
    {
        abort_unless((int) $license->empresa_id === (int) $process->empresa_id && TenantContext::empresaId() === (int) $license->empresa_id, 403);
        if (! Schema::hasTable('licenciamento_processos')) {
            throw new \RuntimeException('Actualize o schema dos vínculos antes de converter.');
        }
        $existing = $this->processId($license);
        if ($existing && $existing !== (int) $process->id) {
            throw new \InvalidArgumentException('Licenciamento já associado a outro processo.');
        }
        if (! $existing) {
            DB::table('licenciamento_processos')->insert(['empresa_id' => $license->empresa_id, 'licenciamento_id' => $license->id, 'processo_id' => $process->id, 'actor_id' => auth()->id(), 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
