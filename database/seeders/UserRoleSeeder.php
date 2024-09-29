<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Criar as roles
    $adminRole = Role::create(['name' => 'Adminstrador']);
    $despachanteRole = Role::create(['name' => 'Despachante']);
    $praticanteRole = Role::create(['name' => 'Particante']);
    $operadorRole = Role::create(['name' => 'Operador']);
    $financeiroRole = Role::create(['name' => 'Financeiro']);
    $auditorRole = Role::create(['name' => 'Auditor']);

    // Criar permissões
    $permissions = [
        'create user',
        'edit user',
        'delete user',
        'view users',
        'create process',
        'edit process',
        'delete process',
        'view documents',
        'generate invoices',
        'view reports',
        'configure system',
        'manage permissions',
        'view activity log',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    // Atribuir permissões aos papéis
    $adminRole->givePermissionTo(Permission::all()); // Admin tem todas as permissões
    $despachanteRole->givePermissionTo(['create process', 'edit process', 'view documents', 'generate invoices']);
    $praticanteRole->givePermissionTo(['create process', 'edit process', 'view documents']);
    $operadorRole->givePermissionTo(['view users', 'view reports']);
    $financeiroRole->givePermissionTo(['generate invoices']);
    $auditorRole->givePermissionTo(['view activity log', 'view reports']);
    }
}
