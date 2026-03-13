<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class TenantContext
{
    /**
     * Resolve current tenant id from the authenticated user.
     * Returns null when no tenant context exists.
     */
    public static function empresaId(): ?int
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        // Security: always resolve tenant from server-side auth state.
        return $user->empresas()->value('empresas.id');
    }
}
