<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        // Todos os utilizadores Autenticados podem ver os clientes
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos os utilizadores Autenticados podem criar clientes
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        // Só pode actualizar se for o criador do cliente ou um administrador ou então utilizador pertecente a mesma empresa do criador

        // Antes deve se verificar se existe factura ou processos associado a este cliente
        if ($customer->invoices()->exists() || $customer->processos()->exists()) {
            return false;
        }

        return 
            $user->id === $customer->user_id || 
            $user->hasRole('admin') ||
            (
                $customer->user &&
                $customer->user->empresas->pluck('id')
                    ->intersect($user->empresas->pluck('id'))
                    ->isNotEmpty()
            );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        // Só pode eliminar se for o criador do cliente ou um administrador ou então utilizador pertecente a mesma empresa do criador
        
        // Antes deve se verificar se existe factura ou processos associado a este cliente
        if ($customer->invoices()->exists() || $customer->processos()->exists()) {
            return false;
        }

        return 
            $user->id === $customer->user_id || 
            $user->hasRole('admin') ||
            (
                $customer->user &&
                $customer->user->empresas->pluck('id')
                    ->intersect($user->empresas->pluck('id'))
                    ->isNotEmpty()
            );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool
    {
        //
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        //
        return $user->hasRole('admin');
    }
}
