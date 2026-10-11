<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/** Pure decision engine. No roles, implicit descendants or database writes. */
final class ModuleEntitlements
{
    public static function eligible(array $subscription, CarbonImmutable $at): bool
    {
        return empty($subscription['deleted_at'])
            && strtolower($subscription['status'] ?? '') === 'ativa'
            && (empty($subscription['data_inicio']) || CarbonImmutable::parse($subscription['data_inicio'])->lte($at))
            && (empty($subscription['data_expiracao']) || CarbonImmutable::parse($subscription['data_expiracao'])->gt($at));
    }

    public static function decide(int $empresa, array $subscriptions, array $planModules, array $activations, CarbonImmutable $at, ?string $legacyUntil): array
    {
        $all = [];
        $valid = [];
        foreach ($subscriptions as $sub) {
            if ((int) $sub['empresa_id'] !== $empresa) {
                continue;
            }
            $all[(int) $sub['id']] = $sub;
            if (self::eligible($sub, $at)) {
                $valid[(int) $sub['id']] = $sub;
            }
        }
        $byModule = [];
        foreach ($activations as $row) {
            if ((int) $row['empresa_id'] === $empresa) {
                $byModule[(int) $row['module_id']][] = $row;
            }
        }
        $candidates = array_keys($byModule);
        foreach ($valid as $sub) {
            $candidates = array_merge($candidates, $planModules[(int) $sub['plano_id']] ?? []);
        }
        $decisions = [];
        foreach (array_unique($candidates) as $module) {
            $module = (int) $module;
            $rows = $byModule[$module] ?? [];
            $decision = ['allowed' => false, 'reason' => 'missing_activation', 'provenance' => null];
            // A known company revocation is never bypassed by a plan or another role.
            if (array_filter($rows, fn ($row) => ! $row['active'])) {
                $decisions[$module] = ['allowed' => false, 'reason' => 'explicit_revocation', 'provenance' => null];
                continue;
            }
            foreach ($rows as $row) {
                $subId = (int) ($row['subscricao_id'] ?? 0);
                if (! $subId || ! isset($valid[$subId])) {
                    continue;
                }
                if ((! empty($row['data_expiracao']) && CarbonImmutable::parse($row['data_expiracao'])->lte($at))
                    || (! empty($row['activation_date']) && CarbonImmutable::parse($row['activation_date'])->gt($at))) {
                    $decision['reason'] = 'activation_outside_validity';
                    continue;
                }
                // Linked active rows are explicit rights, including provisioned extras.
                $decision = ['allowed' => true, 'reason' => 'linked_activation', 'provenance' => $subId];
                break;
            }
            if (! $decision['allowed']) {
                $legacy = array_values(array_filter($rows, fn ($row) => empty($row['subscricao_id'])));
                $contracts = array_filter($valid, fn ($sub) => in_array($module, $planModules[(int) $sub['plano_id']] ?? [], true));
                if (count($legacy) === 1 && count($rows) === 1 && count($contracts) === 1 && count($valid) === 1) {
                    $row = $legacy[0];
                    if ((! empty($row['data_expiracao']) && CarbonImmutable::parse($row['data_expiracao'])->lte($at))
                        || (! empty($row['activation_date']) && CarbonImmutable::parse($row['activation_date'])->gt($at))) {
                        $decision['reason'] = 'activation_outside_validity';
                    } elseif ($legacyUntil && $at->lte(CarbonImmutable::parse($legacyUntil))) {
                        $decision = ['allowed' => true, 'reason' => 'legacy_plan_fallback', 'provenance' => (int) array_key_first($contracts)];
                    } else {
                        $decision['reason'] = 'legacy_window_closed';
                    }
                } elseif ($legacy) {
                    $decision['reason'] = 'ambiguous_legacy';
                } elseif ($rows && $decision['reason'] === 'missing_activation') {
                    $decision['reason'] = 'subscription_ineligible';
                }
            }
            $decisions[$module] = $decision;
        }
        ksort($decisions);

        return $decisions;
    }
}
