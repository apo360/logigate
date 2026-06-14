<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Licenciamento;
use App\Models\User;

final class LicenciamentoPolicy
{
    private function sameTenant(User $user, Licenciamento $licenciamento): bool
    {
        $empresaId = $user->empresas()->value('empresas.id');

        return $empresaId !== null && (int) $licenciamento->empresa_id === (int) $empresaId;
    }

    public function viewAny(User $user): bool
    {
        return (bool) $user->empresas()->value('empresas.id');
    }

    public function view(User $user, Licenciamento $licenciamento): bool
    {
        return $this->sameTenant($user, $licenciamento);
    }

    public function create(User $user): bool
    {
        return (bool) $user->empresas()->value('empresas.id');
    }

    public function update(User $user, Licenciamento $licenciamento): bool
    {
        return $this->sameTenant($user, $licenciamento) && $licenciamento->podeSerEditado();
    }

    public function delete(User $user, Licenciamento $licenciamento): bool
    {
        return $this->sameTenant($user, $licenciamento) && $licenciamento->podeSerEditado();
    }

    public function generateTxt(User $user, Licenciamento $licenciamento): bool
    {
        return $this->sameTenant($user, $licenciamento);
    }

    public function duplicate(User $user, Licenciamento $licenciamento): bool
    {
        return $this->sameTenant($user, $licenciamento);
    }

    public function constituteProcesso(User $user, Licenciamento $licenciamento): bool
    {
        return $this->sameTenant($user, $licenciamento);
    }
}
