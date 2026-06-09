<?php

namespace App\Domains\Empresa\Repositories;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface EmpresaRepositoryInterface
{
    public function find(int $id): ?Empresa;

    public function findOrFail(int $id): Empresa;

    public function currentForUser(User $user): ?Empresa;

    public function listForUser(User $user): Collection;

    public function create(array $attributes): Empresa;

    public function update(Empresa $empresa, array $attributes): Empresa;

    public function delete(Empresa $empresa): void;
}
