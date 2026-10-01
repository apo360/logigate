<?php

namespace App\Domains\Usuarios\Queries;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

final class ListarPermissoesQuery
{
    public function execute(): Collection
    {
        $actor = auth()->user();
        $ids = $actor instanceof \App\Models\User && \App\Support\TenantContext::empresaId($actor)
            ? $actor->getAllPermissions()->pluck('id')->all() : [];
        return Permission::query()->whereIn('id', $ids)
            ->whereNotIn('name', ['permissions.manage', 'menus.manage', 'system.configure'])
            ->orderBy('name')->get();
    }
}
