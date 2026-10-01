<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class CompanyRbac
{
    private static ?int $explicitEmpresa = null;

    public static function isBusinessRole(string $name): bool
    {
        return in_array($name, config('rbac.roles', []), true);
    }

    public static function isBusinessPermission(Permission $permission): bool
    {
        return $permission->guard_name === 'web'
            && str_contains($permission->name, '.')
            && in_array(strstr($permission->name, '.', true), config('rbac.permission_modules', []), true);
    }

    public static function activate(): ?int
    {
        $id = self::$explicitEmpresa ?? TenantContext::empresaId();
        $registrar = app(PermissionRegistrar::class);
        if ($registrar->getPermissionsTeamId() !== $id) {
            $registrar->setPermissionsTeamId($id);
            $registrar->forgetWildcardPermissionIndex();
            $user = auth()->user();
            if ($user instanceof User) {
                self::forgetUser($user);
            }
        }
        return $id;
    }

    /** Explicit scope for transactions/jobs; never chooses a first membership. */
    public static function within(int $empresaId, callable $callback): mixed
    {
        if ($empresaId < 1) {
            throw new AuthorizationException('Empresa RBAC obrigatória.');
        }
        $previous = self::$explicitEmpresa;
        self::$explicitEmpresa = $empresaId;
        self::activate();
        try {
            return $callback();
        } finally {
            self::$explicitEmpresa = $previous;
            self::activate();
        }
    }

    public static function forgetUser(User $user): void
    {
        $user->unsetRelation('roles')->unsetRelation('permissions');
        app(PermissionRegistrar::class)->forgetWildcardPermissionIndex($user);
        $id = getPermissionsTeamId();
        Cache::forget("menus_user_{$user->id}_empresa_{$id}");
    }

    public static function roles(User $actor, array $values): array
    {
        $id = TenantContext::empresaId($actor);
        if (! $id) {
            throw new AuthorizationException('Empresa activa obrigatória.');
        }
        self::activate();
        $admin = $actor->hasRole('Administrador');
        $result = [];
        foreach (array_unique($values) as $value) {
            $query = Role::where('empresa_id', $id)->where('guard_name', 'web');
            $role = is_int($value) || ctype_digit((string) $value)
                ? $query->whereKey($value)->first() : $query->where('name', $value)->first();
            if (! $role || ! self::isBusinessRole($role->name)
                || (! $admin && (! $actor->hasRole($role) || $role->name === 'Administrador'))) {
                throw new AuthorizationException('Papel fora da autoridade da empresa.');
            }
            $result[] = $role;
        }
        return $result;
    }

    public static function permissions(User $actor, array $values): array
    {
        if (! TenantContext::empresaId($actor)) {
            throw new AuthorizationException('Empresa activa obrigatória.');
        }
        self::activate();
        $result = [];
        foreach (array_unique($values) as $value) {
            $query = Permission::where('guard_name', 'web');
            $permission = is_int($value) || ctype_digit((string) $value)
                ? $query->whereKey($value)->first() : $query->where('name', $value)->first();
            // A shared definition is assignable only within the actor's effective authority.
            if (! $permission || ! self::isBusinessPermission($permission) || ! $actor->hasPermissionTo($permission)) {
                throw new AuthorizationException('Permissão fora da autoridade da empresa.');
            }
            $result[] = $permission;
        }
        return $result;
    }
}
