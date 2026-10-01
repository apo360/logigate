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
        $empresaId = \App\Support\TenantContext::empresaId($user);

        return $empresaId !== null && (int) $produto->empresa_id === (int) $empresaId;
    }

    public function viewAny(User $user): bool
    {
        return \App\Support\BusinessAuthorization::allows($user, 'produtos.view');
    }

    public function view(User $user, Produto $produto): bool
    {
        return $this->sameTenant($user, $produto)
            && \App\Support\BusinessAuthorization::allows($user, 'produtos.view');
    }

    public function create(User $user): bool
    {
        return \App\Support\BusinessAuthorization::allows($user, 'produtos.create');
    }

    public function update(User $user, Produto $produto): bool
    {
        return $this->sameTenant($user, $produto)
            && \App\Support\BusinessAuthorization::allows($user, 'produtos.update');
    }

    public function delete(User $user, Produto $produto): bool
    {
        return $this->sameTenant($user, $produto)
            && \App\Support\BusinessAuthorization::allows($user, 'produtos.delete');
    }
}
