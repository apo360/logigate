<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Zero is an unassigned legacy marker, never a selectable Empresa.
        // Preserve every legacy grant; S2.1 must explicitly decide its destination.
        Schema::table('roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->dropUnique('roles_name_guard_name_unique');
            $table->unique(['empresa_id', 'name', 'guard_name']);
        });
        foreach (['model_has_roles' => ['role_id', 'roles'], 'model_has_permissions' => ['permission_id', 'permissions']] as $tableName => [$key, $parent]) {
            Schema::table($tableName, function (Blueprint $table) use ($key, $parent): void {
                $table->unsignedBigInteger('empresa_id')->default(0)->index();
                $table->dropForeign([$key]);
                $table->dropPrimary();
                $table->primary(['empresa_id', $key, 'model_id', 'model_type']);
                $table->foreign($key)->references('id')->on($parent)->cascadeOnDelete();
            });
        }
        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Collapsing company grants can collide and lose authority boundaries.
        throw new RuntimeException('Teams rollback requires a separately reviewed grant reconciliation.');
    }
};
