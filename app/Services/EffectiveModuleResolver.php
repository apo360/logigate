<?php

namespace App\Services;

use App\Support\ModuleEntitlements;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class EffectiveModuleResolver
{
    public function snapshot(int $empresaId): array
    {
        // Explicit company scope works in HTTP and CLI, without a cached tenant.
        $subscriptions = DB::table('subscricoes')->where('empresa_id', $empresaId)->get()->map(fn ($row) => (array) $row)->all();
        $plans = array_unique(array_column($subscriptions, 'plano_id'));
        $planModules = DB::table('plano_modulos')->whereIn('plano_id', $plans)->get()->groupBy('plano_id')->map(fn ($rows) => $rows->pluck('modulo_id')->map(fn ($id) => (int) $id)->unique()->values()->all())->all();
        $activations = DB::table('activated_modules')->where('empresa_id', $empresaId)->get()->map(fn ($row) => (array) $row)->all();

        return compact('subscriptions', 'planModules', 'activations');
    }

    public function resolve(int $empresaId, ?CarbonImmutable $at = null): array
    {
        $data = $this->snapshot($empresaId);

        return ModuleEntitlements::decide($empresaId, $data['subscriptions'], $data['planModules'], $data['activations'], $at ?? CarbonImmutable::now(), config('navigation.legacy_until'));
    }

    public function ids(int $empresaId): array
    {
        return array_keys(array_filter($this->resolve($empresaId), fn ($decision) => $decision['allowed']));
    }

    public function hasSubscription(int $empresaId): bool
    {
        return (bool) array_filter($this->snapshot($empresaId)['subscriptions'], fn ($sub) => ModuleEntitlements::eligible($sub, CarbonImmutable::now()));
    }

    public function moduleId(string $code): ?int
    {
        if (ctype_digit($code)) {
            return DB::table('modules')->where('id', (int) $code)->value('id');
        }
        $names = config('navigation.module_codes.'.$code);
        $matches = $names ? DB::table('modules')->whereIn('module_name', (array) $names)->pluck('id') : collect();

        return $matches->count() === 1 ? (int) $matches->first() : null;
    }
}
