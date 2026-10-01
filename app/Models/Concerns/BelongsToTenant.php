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

            // Registration and Portal use separate creation flows; jobs remain unchanged.
            if (! (\Illuminate\Support\Facades\Auth::user() instanceof \App\Models\User)) {
                return;
            }

            $empresaId = TenantContext::empresaId();
            if (! $empresaId || (! empty($model->{$column}) && (int) $model->{$column} !== $empresaId)) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Ownership fora da empresa ativa.');
            }

            $model->{$column} = $empresaId;
        });
    }
}
