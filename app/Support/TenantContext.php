<?php

namespace App\Support;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

final class TenantContext
{
    /**
     * Resolve current tenant id from the authenticated user.
     * Returns null when no tenant context exists.
     */
    public static function empresaId(?User $actor = null): ?int
    {
        $user = Auth::user();

        if (! $user instanceof User || ($actor && ! $user->is($actor))) {
            return null;
        }

        $value = session('empresa_id');
        if (! is_int($value) && ! is_string($value)) {
            return null;
        }
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id !== false && self::userBelongsToEmpresa($user, $id) ? $id : null;
    }

    public static function empresa(?User $actor = null): ?Empresa
    {
        $id = self::empresaId($actor);

        return $id ? Auth::user()->empresas()->where('empresas.id', $id)->first() : null;
    }

    public static function userBelongsToEmpresa(User $user, int $empresaId): bool
    {
        return $empresaId > 0 && $user->empresas()->where('empresas.id', $empresaId)->exists();
    }

    public static function setEmpresa(User $user, Empresa $empresa): void
    {
        $authenticated = Auth::user();
        if (! $authenticated instanceof User || ! $authenticated->is($user)
            || ! self::userBelongsToEmpresa($user, (int) $empresa->id)) {
            throw new AuthorizationException('Empresa não autorizada para este utilizador.');
        }

        // Validate before mutating: a rejected switch preserves the previous tenant.
        session()->regenerate(true);
        self::clear();
        session()->put('empresa_id', (int) $empresa->id);
        CompanyRbac::activate();
    }

    public static function clear(): void
    {
        session()->forget(['empresa_id', 'empresa_atual_id', 'current_empresa_id', 'empresa.id']);
        $user = Auth::user();
        if ($user instanceof User) {
            $user->unsetRelation('empresas');
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
        CompanyRbac::activate();
    }
}
