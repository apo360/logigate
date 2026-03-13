<?php

namespace App\Models\Scopes;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class TenantScope implements Scope
{
    /**
     * Cache table column checks to avoid repeated schema calls.
     *
     * @var array<string, bool>
     */
    private static array $tenantColumnCache = [];

    public function apply(Builder $builder, Model $model): void
    {
        // Security: keep console/queue flows intact where auth context is absent.
        if (app()->runningInConsole()) {
            return;
        }

        $empresaId = TenantContext::empresaId();

        // Security: fail closed when tenant context is missing.
        if (! $empresaId) {
            return;
        }

        // Allow model-specific tenant strategy when needed.
        if (method_exists($model, 'applyTenantConstraint')) {
            $model->applyTenantConstraint($builder, $empresaId);
            return;
        }

        $column = method_exists($model, 'getTenantColumn')
            ? $model->getTenantColumn()
            : 'empresa_id';

        if (!$this->tableHasColumn($model->getTable(), $column)) {
            return;
        }

        $builder->where($model->qualifyColumn($column), $empresaId);
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        $cacheKey = "{$table}:{$column}";

        if (!array_key_exists($cacheKey, self::$tenantColumnCache)) {
            self::$tenantColumnCache[$cacheKey] = Schema::hasColumn($table, $column);
        }

        return self::$tenantColumnCache[$cacheKey];
    }
}
