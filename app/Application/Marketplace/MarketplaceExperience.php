<?php

namespace App\Application\Marketplace;

use App\Models\MarketplaceProfile;
use App\Models\PautaAduaneira;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

final class MarketplaceExperience
{
    public function window(int $months): array
    {
        // Complete calendar months: allows exact recombination of monthly aggregates.
        $end = now()->startOfMonth();
        return [$end->copy()->subMonths($months), $end];
    }

    public function selection(?int $id, ?string $codigo, ?string $description = null): ?PautaAduaneira
    {
        if (!$id && !$codigo) return null;
        $query = PautaAduaneira::query();
        if ($id) $query->whereKey($id);
        if ($codigo) $query->where('codigo', $codigo);
        $item = $query->first();
        if (!$item) throw ValidationException::withMessages(['pauta_id' => 'A mercadoria já não corresponde à pauta. Seleccione-a novamente.']);
        if ($description !== null && $description !== (string) $item->descricao) throw ValidationException::withMessages(['mercadoria_descricao' => 'A descrição da mercadoria mudou. Seleccione-a novamente.']);
        // Never choose one silently if a code has multiple catalogue identities.
        if (!$id && PautaAduaneira::where('codigo', $codigo)->count() !== 1) {
            throw ValidationException::withMessages(['codigo' => 'Código ambíguo. Seleccione a identidade pautal na pesquisa.']);
        }
        return $item;
    }

    public function search(?PautaAduaneira $item, array $filters, int $perPage = 12): array
    {
        $months = (int) ($filters['months'] ?? config('marketplace.window_months'));
        [$start, $end] = $this->window($months);
        $empty = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, (int) ($filters['page'] ?? 1), ['path' => request()->url()]);
        if (!Schema::hasTable('marketplace_profiles')) return ['profiles' => $empty, 'ready' => false, 'start' => $start, 'end' => $end];

        $history = DB::table('marketplace_activity_months')->select('profile_id')
            ->selectRaw('SUM(operations) AS operations, COUNT(DISTINCT month) AS active_months, MAX(last_operation) AS last_operation')
            ->where('month', '>=', $start->toDateString())->where('month', '<', $end->toDateString())
            ->when($item, fn ($q) => $q->where('pauta_id', $item->id)->where('codigo', (string) $item->codigo))
            ->when(!$item, fn ($q) => $q->whereRaw('1 = 0'))
            ->groupBy('profile_id');
        $revision = config('marketplace.catalogue_revision');
        $query = DB::table('marketplace_profiles as p')
            ->join('empresas as e', 'e.id', '=', 'p.empresa_id')
            ->leftJoinSub($history, 'h', function ($join) use ($revision) {
                $join->on('h.profile_id', '=', 'p.id')->where('p.history_authorized', true)
                    ->whereNotNull('p.history_reviewed_at')->where('p.catalogue_revision', '=', $revision ?: '__unconfigured__')
                    ->where('p.calculated_at', '>=', now()->subHours(config('marketplace.summary_max_age_hours')));
            })
            ->where('p.published', true)->where('p.service_provider', true)->whereNotNull('p.consent_reference')
            ->where('p.consent_reference', '<>', '')->where('e.ativo', 1)
            ->whereIn('e.Designacao', ['Despachante Oficial', 'Praticante']);
        $declared = DB::table('marketplace_specialties')->select('profile_id')
            ->when($item, fn ($q) => $q->where('pauta_id', $item->id)->where('codigo', (string) $item->codigo))
            ->when(!$item, fn ($q) => $q->whereRaw('1 = 0'));
        $query->where(function ($q) use ($item, $declared) {
            if ($item) $q->whereNotNull('h.profile_id')->orWhereIn('p.id', $declared);
        });
        if (!empty($filters['location'])) $query->where('p.public_location', $filters['location']);
        if (!empty($filters['recurrence'])) $query->where('h.active_months', '>=', (int) $filters['recurrence']);
        $query->select(['p.id', 'p.public_name', 'p.public_location', 'h.operations', 'h.active_months', 'h.last_operation'])
            ->orderByRaw('CASE WHEN h.profile_id IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('h.active_months')->orderByDesc('h.last_operation')->orderByDesc('h.operations')->orderBy('p.id');
        return ['profiles' => $query->paginate($perPage, ['*'], 'page', (int) ($filters['page'] ?? 1))->withQueryString(), 'ready' => true, 'start' => $start, 'end' => $end];
    }

    /** Console-only authorised projection; no scopes are removed, and ownership is explicit. */
    public function refresh(MarketplaceProfile $profile): void
    {
        if (!app()->runningInConsole()) throw new \LogicException('Projection is restricted to the local command.');
        $revision = config('marketplace.catalogue_revision');
        if (!$revision || !$profile->service_provider || !$profile->history_authorized || !$profile->history_reviewed_at || !$profile->consent_reference) {
            throw new \LogicException('Consentimento, revisão do histórico e versão da pauta são obrigatórios.');
        }
        [$start, $end] = $this->window((int) config('marketplace.window_months'));
        // DISTINCT avoids duplication from repeated commodity lines; licenses are not summed.
        $operations = Processo::query()->where('processos.empresa_id', $profile->empresa_id)
            ->where('processos.Estado', 'Finalizado')
            ->where('processos.DataFecho', '>=', $start->toDateString())->where('processos.DataFecho', '<', $end->toDateString())
            ->whereNotNull('processos.DataAbertura')->whereColumn('processos.DataFecho', '>=', 'processos.DataAbertura')
            ->join('mercadorias as m', 'm.Fk_Importacao', '=', 'processos.id')
            ->join('pauta_aduaneira as a', 'a.id', '=', 'm.pauta_aduaneira_id')
            ->whereColumn('m.codigo_pautal_snapshot', 'a.codigo')->whereNotNull('m.pauta_snapshot_at')
            ->selectRaw("? AS profile_id, a.id AS pauta_id, a.codigo AS codigo, DATE_FORMAT(processos.DataFecho, '%Y-%m-01') AS month, COUNT(DISTINCT processos.id) AS operations, MAX(DATE(processos.DataFecho)) AS last_operation", [$profile->id])
            ->groupBy('a.id', 'a.codigo')->groupByRaw("DATE_FORMAT(processos.DataFecho, '%Y-%m-01')");
        DB::transaction(function () use ($profile, $operations, $revision) {
            MarketplaceProfile::whereKey($profile->id)->lockForUpdate()->firstOrFail();
            DB::table('marketplace_activity_months')->where('profile_id', $profile->id)->delete();
            DB::table('marketplace_activity_months')->insertUsing(['profile_id', 'pauta_id', 'codigo', 'month', 'operations', 'last_operation'], $operations->toBase());
            $profile->update(['catalogue_revision' => $revision, 'calculated_at' => now()]);
        });
    }
}
