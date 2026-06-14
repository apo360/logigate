<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class CustomerPolicy
{
    /**
     * Security: only users from the same tenant can access a customer.
     */
    private function belongsToTenant(User $user, Customer $customer): bool
    {
        $empresaId = $user->empresas()->value('empresas.id');

        if (!$empresaId) {
            return false;
        }

        if ((int) $customer->empresa_id === (int) $empresaId) {
            return true;
        }

        return Schema::hasTable('customers_empresas')
            && $customer->empresas()->where('empresas.id', $empresaId)->exists();
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->empresas()->value('empresas.id');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        return $this->belongsToTenant($user, $customer);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return (bool) $user->empresas()->value('empresas.id');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        if (!$this->belongsToTenant($user, $customer)) {
            return false;
        }

        if ($this->hasBlockingRelations($customer)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        if (!$this->belongsToTenant($user, $customer)) {
            return false;
        }

        if ($this->hasBlockingRelations($customer)) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool
    {
        return $this->belongsToTenant($user, $customer);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return false;
    }

    private function hasBlockingRelations(Customer $customer): bool
    {
        if (Schema::hasTable('sales_invoices') && $customer->invoices()->exists()) {
            return true;
        }

        return Schema::hasTable('processos') && $customer->processos()->exists();
    }
}
