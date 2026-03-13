<?php

namespace App\Policies;

use App\Models\Produto;
use App\Models\User;

class ProdutoPolicy
{
    /**
     * Security: products are tenant-owned by empresa_id.
     */
    private function sameTenant(User $user, Produto $produto): bool
    {
        $empresaId = $user->empresas()->value('empresas.id');

        return $empresaId !== null && (int) $produto->empresa_id === (int) $empresaId;
    }

    public function viewAny(User $user): bool
    {
        return (bool) $user->empresas()->value('empresas.id');
    }

    public function view(User $user, Produto $produto): bool
    {
        return $this->sameTenant($user, $produto);
    }

    public function create(User $user): bool
    {
        return (bool) $user->empresas()->value('empresas.id');
    }

    public function update(User $user, Produto $produto): bool
    {
        return $this->sameTenant($user, $produto);
    }

    public function delete(User $user, Produto $produto): bool
    {
        return $this->sameTenant($user, $produto);
    }
}
