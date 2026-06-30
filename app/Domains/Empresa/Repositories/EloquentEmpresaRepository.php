<?php

namespace App\Domains\Empresa\Repositories;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class EloquentEmpresaRepository implements EmpresaRepositoryInterface
{
    public function find(int $id): ?Empresa
    {
        return Empresa::find($id);
    }

    public function findOrFail(int $id): Empresa
    {
        return Empresa::findOrFail($id);
    }

    public function currentForUser(User $user): ?Empresa
    {
        return $user->empresaAtiva();
    }

    public function listForUser(User $user): Collection
    {
        return $user->empresas()->get();
    }

    public function create(array $attributes): Empresa
    {
        return Empresa::create($attributes);
    }

    public function update(Empresa $empresa, array $attributes): Empresa
    {
        $empresa->fill($attributes);
        $empresa->save();

        return $empresa->refresh();
    }

    public function delete(Empresa $empresa): void
    {
        $empresa->delete();
    }
}
