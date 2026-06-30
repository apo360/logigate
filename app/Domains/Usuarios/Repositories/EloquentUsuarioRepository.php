<?php

namespace App\Domains\Usuarios\Repositories;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class EloquentUsuarioRepository implements UsuarioRepositoryInterface
{
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    public function findOrFail(int $id): User
    {
        return User::findOrFail($id);
    }

    public function queryForEmpresa(Empresa $empresa): Builder
    {
        return $empresa->users()->select('users.*')->getQuery();
    }

    public function listForEmpresa(Empresa $empresa, ?string $search = null): Collection
    {
        return $this->queryForEmpresa($empresa)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%");
                });
            })
            ->with('roles', 'permissions')
            ->orderBy('users.name')
            ->get();
    }

    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    public function update(User $user, array $attributes): User
    {
        $user->forceFill($attributes)->save();

        return $user->refresh();
    }

    public function belongsToEmpresa(User $user, Empresa $empresa): bool
    {
        return $user->empresas()->where('empresas.id', $empresa->id)->exists();
    }
}
