<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // Definitions only: never infer or grant authority to existing users/roles.
        foreach (['exportadores', 'produtos'] as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $operation) {
                DB::table('permissions')->insertOrIgnore([
                    'name' => $module.'.'.$operation,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        throw new LogicException('Permission catalog rollback requires explicit grant reconciliation.');
    }
};
