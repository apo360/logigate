<?php

namespace App\Application\Empresa\Actions;

use App\Models\User;
use App\Support\CompanyRbac;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class ConsolidarLegacyRbacAction
{
    public function execute(): array
    {
        if (! app()->environment('testing') || DB::connection()->getDatabaseName() !== 'logigate_testing') {
            throw new \RuntimeException('APP_ENV=testing and DB_DATABASE=logigate_testing required.');
        }
        if (DB::connection()->getDriverName() !== 'mysql'
            || DB::selectOne('SELECT DATABASE() AS selected_database')->selected_database !== 'logigate_testing') {
            throw new \RuntimeException('Connected database must be logigate_testing.');
        }
        $counts = array_fill_keys(['Users processed', 'Team roles migrated', 'Direct permissions migrated',
            'Membership roles migrated', 'Ambiguous grants skipped', 'Conflicts skipped', 'Already migrated', 'Errors'], 0);
        User::query()->select('id')->orderBy('id')->chunkById(100, function ($users) use (&$counts): void {
            foreach ($users as $user) {
                try {
                    $delta = DB::transaction(fn () => $this->convertUser((int) $user->id));
                    foreach ($delta as $key => $value) $counts[$key] += $value;
                } catch (\Throwable) {
                    // Atomic per User; keep processing others without exposing PII/SQL.
                    $counts['Errors']++;
                }
                $counts['Users processed']++;
            }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $actor = auth()->user();
        if ($actor instanceof User) CompanyRbac::forgetUser($actor);
        CompanyRbac::activate();
        return $counts;
    }

    private function convertUser(int $id): array
    {
        $counts = array_fill_keys(['Team roles migrated', 'Direct permissions migrated', 'Membership roles migrated',
            'Ambiguous grants skipped', 'Conflicts skipped', 'Already migrated'], 0);
        // Serialize repeated invocations and membership changes for the same User.
        $user = User::query()->lockForUpdate()->findOrFail($id);
        $memberships = DB::table('empresa_users')->where('user_id', $id)->lockForUpdate()->get();
        $legacyRoles = Role::query()->whereNull('roles.empresa_id')->where('guard_name', 'web')
            ->join('model_has_roles as legacy', 'legacy.role_id', '=', 'roles.id')
            ->where('legacy.model_type', $user->getMorphClass())->where('legacy.model_id', $id)
            ->where('legacy.empresa_id', 0)->select('roles.*')->get();
        $legacyPermissions = Permission::query()->join('model_has_permissions as legacy', 'legacy.permission_id', '=', 'permissions.id')
            ->where('legacy.model_type', $user->getMorphClass())->where('legacy.model_id', $id)
            ->where('legacy.empresa_id', 0)->select('permissions.*')->get();
        $single = $memberships->count() === 1;
        if (! $single) $counts['Ambiguous grants skipped'] += $legacyRoles->count() + $legacyPermissions->count();
        foreach ($memberships as $membership) {
            $empresaId = (int) $membership->empresa_id;
            if ($empresaId < 1) { $counts['Conflicts skipped']++; continue; }
            CompanyRbac::within($empresaId, function () use ($membership, $single, $legacyRoles, $legacyPermissions, $user, &$counts): void {
                CompanyRbac::forgetUser($user);
                $existingRoles = $user->roles()->get();
                $existingPermissions = $user->permissions()->get();
                $hasExisting = $existingRoles->isNotEmpty() || $existingPermissions->isNotEmpty();
                $explicit = $membership->role !== null && trim($membership->role) !== '';
                $names = $explicit ? [trim($membership->role)] : ($single ? $legacyRoles->pluck('name')->all() : []);
                foreach (array_unique($names) as $name) {
                    if (! CompanyRbac::isBusinessRole($name)) { $counts['Conflicts skipped']++; continue; }
                    if ($hasExisting) {
                        $counts[$existingRoles->contains('name', $name) ? 'Already migrated' : 'Conflicts skipped']++;
                        continue;
                    }
                    $role = Role::where('empresa_id', $membership->empresa_id)->where('guard_name', 'web')->where('name', $name)->first();
                    if (! $role) {
                        $templates = Role::whereNull('empresa_id')->where('guard_name','web')->where('name', $name)->get();
                        if ($templates->count() !== 1) { $counts['Conflicts skipped']++; continue; }
                        $role = Role::query()->create(['empresa_id'=>$membership->empresa_id,'guard_name'=>'web','name'=>$name]);
                        // Never expand an existing company's role definition from legacy data.
                        $permissions = $templates->sole()->permissions->filter(fn ($p) => CompanyRbac::isBusinessPermission($p));
                        $role->syncPermissions($permissions);
                    }
                    $user->assignRole($role);
                    $counts['Team roles migrated']++;
                    if ($explicit) $counts['Membership roles migrated']++;
                }
                if ($single) foreach ($legacyPermissions as $permission) {
                    if (! CompanyRbac::isBusinessPermission($permission)) { $counts['Conflicts skipped']++; continue; }
                    if ($hasExisting) {
                        $counts[$existingPermissions->contains('id',$permission->id) ? 'Already migrated' : 'Conflicts skipped']++;
                        continue;
                    }
                    $user->givePermissionTo($permission);
                    $counts['Direct permissions migrated']++;
                }
                CompanyRbac::forgetUser($user);
            });
        }
        return $counts;
    }
}
