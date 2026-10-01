<?php

namespace App\Policies;

use App\Models\Exportador;
use App\Models\User;
use App\Support\BusinessAuthorization;
use App\Support\TenantContext;

class ExportadorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
        return BusinessAuthorization::allows($user, 'exportadores.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Exportador $exportador): bool
    {
        //
        return $this->sameEmpresa($user, $exportador) && BusinessAuthorization::allows($user, 'exportadores.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
        return BusinessAuthorization::allows($user, 'exportadores.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Exportador $exportador): bool
    {
        //
        return $this->sameEmpresa($user, $exportador) && BusinessAuthorization::allows($user, 'exportadores.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Exportador $exportador): bool
    {
        //
        return $this->sameEmpresa($user, $exportador) && BusinessAuthorization::allows($user, 'exportadores.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Exportador $exportador): bool
    {
        //
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Exportador $exportador): bool
    {
        //
        return false;
    }

    public function updateProfile(User $user, Exportador $exportador): bool
    {
        $id = TenantContext::empresaId($user);
        return $this->update($user, $exportador)
            && (int) $exportador->empresa_id === $id
            && ! $exportador->empresas()->where('empresas.id', '!=', $id)->exists();
    }

    private function sameEmpresa(User $user, Exportador $exportador): bool
    {
        $id = TenantContext::empresaId($user);
        // The module lists/changes company associations. Legacy owner metadata
        // cannot restore authority after that association has been removed.
        return $id !== null && $exportador->empresas()->where('empresas.id', $id)->exists();
    }
}
