<?php

namespace App\Domains\Usuarios\Queries;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

final class ListarPermissoesQuery
{
    public function execute(): Collection
    {
        return Permission::query()->orderBy('name')->get();
    }
}
