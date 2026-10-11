<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ModuleReconciliation
{
    public static function fingerprint(array $row): string
    {
        ksort($row);
        return hash('sha256', json_encode($row, JSON_THROW_ON_ERROR));
    }

    public function preview(int $empresa): array
    {
        $data = app(EffectiveModuleResolver::class)->snapshot($empresa);
        $result = ['empresa_id' => $empresa, 'changes' => [], 'review' => []];
        foreach ($data['activations'] as $row) {
            if ($row['subscricao_id']) {
                continue;
            }
            // Match historical origin, including superseded contracts; never latest().
            $candidates = array_filter($data['subscriptions'], function ($sub) use ($row, $data) {
                if (! $row['activation_date'] || ! $sub['data_inicio']
                    || ! in_array((int) $row['module_id'], $data['planModules'][(int) $sub['plano_id']] ?? [], true)) {
                    return false;
                }
                $activation = CarbonImmutable::parse($row['activation_date']);

                return $activation->gte(CarbonImmutable::parse($sub['data_inicio']))
                    && (! $sub['data_expiracao'] || $activation->lt(CarbonImmutable::parse($sub['data_expiracao'])));
            });
            $duplicates = array_filter($data['activations'], fn ($other) => (int) $other['module_id'] === (int) $row['module_id']);
            if (! $row['active'] || count($candidates) !== 1 || count($duplicates) !== 1) {
                $result['review'][] = ['activation_id' => $row['id'], 'reason' => ! $row['active'] ? 'explicit_revocation' : 'origin_not_proven'];
                continue;
            }
            $sub = reset($candidates);
            if (! empty($sub['deleted_at']) || strtolower($sub['status']) !== 'ativa'
                || (! empty($row['data_expiracao']) && $row['data_expiracao'] !== $sub['data_expiracao'])) {
                $result['review'][] = ['activation_id' => $row['id'], 'reason' => 'validity_requires_review'];
                continue;
            }
            $result['changes'][] = ['activation_id' => (int) $row['id'], 'subscricao_id' => (int) $sub['id'],
                'data_expiracao' => $sub['data_expiracao'], 'before' => self::fingerprint($row),
                'before_values' => ['subscricao_id' => $row['subscricao_id'], 'data_expiracao' => $row['data_expiracao']],
                'subscription_before' => self::fingerprint($sub)];
        }

        return $result;
    }

    public function apply(array $manifest): int
    {
        $empresa = (int) ($manifest['empresa_id'] ?? 0);
        if ($empresa < 1 || ! isset($manifest['changes']) || ! is_array($manifest['changes'])) {
            throw new RuntimeException('Manifest inválido.');
        }
        return DB::transaction(function () use ($manifest, $empresa) {
            // Lock company contracts and activations in the same order as provisioning.
            DB::table('subscricoes')->where('empresa_id', $empresa)->lockForUpdate()->get();
            DB::table('activated_modules')->where('empresa_id', $empresa)->lockForUpdate()->get();
            $eligible = array_column($this->preview($empresa)['changes'], null, 'activation_id');
            $count = 0;
            $seen = [];
            foreach ($manifest['changes'] as $change) {
                $id = (int) ($change['activation_id'] ?? 0);
                if (isset($seen[$id])) {
                    throw new RuntimeException('Manifest contém IDs duplicados.');
                }
                $seen[$id] = true;
                $row = DB::table('activated_modules')->where('empresa_id', $empresa)->where('id', $id)->first();
                if ($row && (int) $row->subscricao_id === (int) ($change['subscricao_id'] ?? 0)
                    && $row->data_expiracao === ($change['data_expiracao'] ?? null)) {
                    continue; // Replaying the exact manifest has no effect.
                }
                if (! isset($eligible[$id]) || $eligible[$id] !== $change) {
                    throw new RuntimeException('Origem ou snapshot mudou; repita a simulação.');
                }
                DB::table('activated_modules')->where('id', $id)->where('empresa_id', $empresa)->whereNull('subscricao_id')
                    ->update(['subscricao_id' => $change['subscricao_id'], 'data_expiracao' => $change['data_expiracao'], 'updated_at' => now()]);
                $count++;
            }

            return $count;
        });
    }
}
