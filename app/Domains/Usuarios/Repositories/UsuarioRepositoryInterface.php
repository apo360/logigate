<?php

namespace App\Domains\Usuarios\Repositories;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UsuarioRepositoryInterface
{
    public function find(int $id): ?User;

    public function findOrFail(int $id): User;

    public function listForEmpresa(Empresa $empresa, ?string $search = null): Collection;

    public function create(array $attributes): User;

    public function update(User $user, array $attributes): User;

    public function belongsToEmpresa(User $user, Empresa $empresa): bool;
}
