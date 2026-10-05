<?php
namespace App\Domains\Processo\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class OperationalSequence
{
    public function reserve(int $empresaId, string $kind, int $year): int
    {
        abort_unless(\App\Support\TenantContext::empresaId() === $empresaId, 403);
        if (! Schema::hasTable('operational_sequences')) {
            throw new \RuntimeException('Actualize o schema das séries operacionais antes de emitir números.');
        }
        if (! in_array($kind, ['processo', 'conta_despacho'], true) || $year < 2000 || $year > 9999) {
            throw new \InvalidArgumentException('Série operacional inválida.');
        }
        return DB::transaction(function () use ($empresaId, $kind, $year) {
            // A company row exists even for the first emission; lock order is stable.
            DB::table('empresas')->where('id', $empresaId)->lockForUpdate()->firstOrFail();
            DB::table('operational_sequences')->insertOrIgnore(['empresa_id' => $empresaId, 'kind' => $kind, 'year' => $year, 'value' => 0]);
            $query = DB::table('operational_sequences')->where('empresa_id', $empresaId)->where('kind', $kind)->where('year', $year);
            $counter = (clone $query)->lockForUpdate()->first();
            $records = DB::table('processos')->where('empresa_id', $empresaId);
            if ($kind === 'processo') {
                $maximum = $records->where('NrProcesso', 'regexp', '^PROC-' . $year . '-[0-9]{6}$')->selectRaw('MAX(CAST(RIGHT(NrProcesso, 6) AS UNSIGNED)) AS maximum')->value('maximum');
            } else {
                // The suffix records emission year; created_at must not choose the series.
                $maximum = $records->where('ContaDespacho', 'regexp', '^CCD-[0-9]+/' . $year . '$')->selectRaw("MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(ContaDespacho, '/', 1), '-', -1) AS UNSIGNED)) AS maximum")->value('maximum');
            }
            $next = max((int) $counter->value, (int) $maximum) + 1;
            if ($kind === 'processo' && $next > 999999) { throw new \RuntimeException('Série de processos esgotada.'); }
            $query->update(['value' => $next]);
            return $next;
        }, 3);
    }
}
