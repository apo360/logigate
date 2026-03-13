<?php

namespace App\Policies;

use App\Models\Processo;
use App\Models\User;

class ProcessoPolicy
{
    /**
     * Security: process access is restricted to the user's tenant.
     */
    private function sameTenant(User $user, Processo $processo): bool
    {
        $empresaId = $user->empresas()->value('empresas.id');

        return $empresaId !== null && (int) $processo->empresa_id === (int) $empresaId;
    }

    public function viewAny(User $user): bool
    {
        return (bool) $user->empresas()->value('empresas.id');
    }

    public function view(User $user, Processo $processo): bool
    {
        return $this->sameTenant($user, $processo);
    }

    public function create(User $user): bool
    {
        return (bool) $user->empresas()->value('empresas.id');
    }

    public function update(User $user, Processo $processo): bool
    {
        return $this->sameTenant($user, $processo);
    }

    public function delete(User $user, Processo $processo): bool
    {
        return $this->sameTenant($user, $processo);
    }
}
