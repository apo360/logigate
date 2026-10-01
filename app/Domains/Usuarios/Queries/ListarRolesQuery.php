<?php

namespace App\Domains\Usuarios\Queries;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

final class ListarRolesQuery
{
    public function execute(): Collection
    {
        $id = \App\Support\CompanyRbac::activate();
        return Role::query()->where('empresa_id', $id)->when(! $id, fn ($q) => $q->whereRaw('1=0'))->orderBy('name')->get();
    }
}
