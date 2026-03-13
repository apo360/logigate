<?php

namespace App\Policies;

use App\Models\SalesInvoice;
use App\Models\User;

class SalesInvoicePolicy
{
    /**
     * Security: document access must stay inside tenant boundary.
     */
    private function sameTenant(User $user, SalesInvoice $invoice): bool
    {
        $empresaId = $user->empresas()->value('empresas.id');

        return $empresaId !== null && (int) $invoice->empresa_id === (int) $empresaId;
    }

    public function viewAny(User $user): bool
    {
        return (bool) $user->empresas()->value('empresas.id');
    }

    public function view(User $user, SalesInvoice $invoice): bool
    {
        return $this->sameTenant($user, $invoice);
    }

    public function create(User $user): bool
    {
        return (bool) $user->empresas()->value('empresas.id');
    }

    public function update(User $user, SalesInvoice $invoice): bool
    {
        return $this->sameTenant($user, $invoice);
    }

    public function delete(User $user, SalesInvoice $invoice): bool
    {
        return $this->sameTenant($user, $invoice);
    }
}
