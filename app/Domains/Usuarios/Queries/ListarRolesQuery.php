<?php

namespace App\Domains\Usuarios\Queries;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

final class ListarRolesQuery
{
    public function execute(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }
}
