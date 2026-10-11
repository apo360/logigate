<?php

namespace App\Services;

use App\Models\Subscricao;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class BillingQuota
{
    public function assertAvailable(int $empresaId, string $resource, int $additional = 1): void
    {
        // Monthly process usage needs an immutable ledger and approval of transition/import rules.
        // Keep creation available until that policy is reviewed; never infer usage from surviving rows.
        if ($resource === 'processes') {
            return;
        }
        $sub = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->where('empresa_id', $empresaId)->effective()->latest('id')->first();
        // Preserve grandfathered contracts and access until their commercial limits are reviewed.
        if (! $sub?->commercial_snapshot) {
            return;
        }
        $limit = data_get($sub->commercial_snapshot, 'limits.'.$resource);
        if ($limit === null || $limit < 0) {
            return;
        }
        $used = match ($resource) {
            'users' => DB::table('empresa_users')->where('empresa_id', $empresaId)->distinct()->count('user_id'),
            'storage_gb' => DB::table('documentos_arquivos')->where('empresa_id', $empresaId)->whereNull('deleted_at')->sum('size_bytes'),
            default => throw new \InvalidArgumentException('Quota desconhecida.'),
        };
        $maximum = $resource === 'storage_gb' ? $limit * 1024 * 1024 * 1024 : $limit;
        if ($used + $additional > $maximum) {
            throw ValidationException::withMessages(['plan_limit' => 'O limite do plano para este recurso foi atingido. Consulte Subscrição e Pagamentos.']);
        }
    }
}
