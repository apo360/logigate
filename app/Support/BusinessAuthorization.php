<?php

namespace App\Support;

use App\Models\User;
use Throwable;

final class BusinessAuthorization
{
    public static function allows(User $user, string $permission): bool
    {
        try {
            return TenantContext::empresaId($user) !== null
                && $user->hasPermissionTo($permission, 'web');
        } catch (Throwable) {
            // Unknown permissions and failed authority resolution never grant access.
            return false;
        }
    }

    public static function any(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::allows($user, $permission)) {
                return true;
            }
        }

        return false;
    }
}
