<?php

namespace App\Models\Concerns;

use App\Support\CompanyRbac;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;

trait HasCompanyRoles
{
    use HasRoles {
        roles as private spatieRoles;
        permissions as private spatiePermissions;
        hasRole as private spatieHasRole;
        hasPermissionTo as private spatieHasPermissionTo;
        assignRole as private spatieAssignRole;
        syncRoles as private spatieSyncRoles;
        removeRole as private spatieRemoveRole;
        givePermissionTo as private spatieGivePermissionTo;
        syncPermissions as private spatieSyncPermissions;
        revokePermissionTo as private spatieRevokePermissionTo;
    }

    private ?int $loadedRbacEmpresa = null;

    private function synchronizeCompanyRoles(): ?int
    {
        $id = CompanyRbac::activate();
        if ($this->loadedRbacEmpresa !== $id) {
            $this->loadedRbacEmpresa = $id;
            CompanyRbac::forgetUser($this);
        }
        return $id;
    }

    public function getRelationValue($key)
    {
        if (in_array($key, ['roles', 'permissions'], true)) {
            $this->synchronizeCompanyRoles();
        }
        return parent::getRelationValue($key);
    }

    public function roles(): BelongsToMany
    {
        $this->synchronizeCompanyRoles();
        return $this->spatieRoles()->where('roles.empresa_id', getPermissionsTeamId());
    }

    public function permissions(): BelongsToMany
    {
        $this->synchronizeCompanyRoles();
        return $this->spatiePermissions();
    }

    public function hasRole($roles, ?string $guard = null): bool
    {
        return $this->synchronizeCompanyRoles() !== null && $this->spatieHasRole($roles, $guard);
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        return $this->synchronizeCompanyRoles() !== null && $this->spatieHasPermissionTo($permission, $guardName);
    }

    private function requireCompanyGrantContext(): int
    {
        $id = $this->synchronizeCompanyRoles();
        if (! $id || ! \App\Support\TenantContext::userBelongsToEmpresa($this, $id)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Membership e empresa RBAC obrigatórias.');
        }
        return $id;
    }

    private function companyRoleValues(array $values): array
    {
        $id = $this->requireCompanyGrantContext();
        return collect($values)->flatten()->map(function ($value) use ($id) {
            if ($value instanceof \BackedEnum) $value = $value->value;
            $key = $value instanceof \Spatie\Permission\Contracts\Role ? $value->getKey() : $value;
            $query = \Spatie\Permission\Models\Role::where('empresa_id', $id)->where('guard_name','web');
            $role = is_int($key) || ctype_digit((string)$key) ? $query->whereKey($key)->first() : $query->where('name',$key)->first();
            if (! $role || ! CompanyRbac::isBusinessRole($role->name)) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Role empresarial inválida.');
            }
            return $role;
        })->all();
    }

    private function companyPermissionValues(array $values): array
    {
        $this->requireCompanyGrantContext();
        return collect($values)->flatten()->map(function ($value) {
            if ($value instanceof \BackedEnum) $value = $value->value;
            $key = $value instanceof \Spatie\Permission\Contracts\Permission ? $value->getKey() : $value;
            $query = \Spatie\Permission\Models\Permission::where('guard_name','web');
            $permission = is_int($key) || ctype_digit((string)$key) ? $query->whereKey($key)->first() : $query->where('name',$key)->first();
            if (! $permission || ! CompanyRbac::isBusinessPermission($permission)) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Permission empresarial inválida.');
            }
            return $permission;
        })->all();
    }

    public function assignRole(...$roles)
    {
        return $this->spatieAssignRole($this->companyRoleValues($roles));
    }

    public function syncRoles(...$roles)
    {
        return $this->spatieSyncRoles($this->companyRoleValues($roles));
    }

    public function removeRole($role)
    {
        return $this->spatieRemoveRole($this->companyRoleValues([$role])[0]);
    }

    public function givePermissionTo(...$permissions)
    {
        return $this->spatieGivePermissionTo($this->companyPermissionValues($permissions));
    }

    public function syncPermissions(...$permissions)
    {
        return $this->spatieSyncPermissions($this->companyPermissionValues($permissions));
    }

    public function revokePermissionTo($permission)
    {
        foreach ($this->companyPermissionValues([$permission]) as $value) $this->spatieRevokePermissionTo($value);
        return $this;
    }
}
