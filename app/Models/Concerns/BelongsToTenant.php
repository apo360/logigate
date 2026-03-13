<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Support\TenantContext;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model): void {
            if (app()->runningInConsole()) {
                return;
            }

            $column = method_exists($model, 'getTenantColumn')
                ? $model->getTenantColumn()
                : 'empresa_id';

            // Security: force tenant ownership from authenticated context.
            if (empty($model->{$column})) {
                $model->{$column} = TenantContext::empresaId();
            }
        });
    }
}
