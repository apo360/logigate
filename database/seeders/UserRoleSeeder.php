<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Utilizadores
            'users.view', 'users.create', 'users.update', 'users.delete',

            // Empresas
            'empresas.view', 'empresas.create', 'empresas.update', 'empresas.delete',

            // Clientes
            'customers.view', 'customers.create', 'customers.update', 'customers.delete',
            'customers.activate', 'customers.deactivate',
            'customers.manage_portal_credentials',
            'customers.view_processos', 'customers.view_licenciamentos',
            'customers.view_documents', 'customers.associate_empresa',

            // Processos
            'processos.view', 'processos.create', 'processos.update', 'processos.delete',
            'processos.manage_mercadorias',

            // Licenciamentos
            'licenciamentos.view', 'licenciamentos.create', 'licenciamentos.update',
            'licenciamentos.delete', 'licenciamentos.manage_mercadorias',

            // Mercadorias
            'mercadorias.view', 'mercadorias.create', 'mercadorias.update', 'mercadorias.delete',

            // Documentos
            'documents.view', 'documents.create', 'documents.update',
            'documents.delete', 'documents.download',

            // Facturação
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete',

            // Pagamentos
            'payments.view', 'payments.create', 'payments.update', 'payments.delete',

            // Recibos
            'receipts.view',

            // Relatórios
            'reports.view',

            // Auditoria
            'audit.view',

            // Permissões (SOMENTE Administrador)
            'permissions.view', 'permissions.manage',

            // Sistema (SOMENTE Administrador)
            'menus.manage', 'system.configure',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        $roles = [
            'Administrador', 'Gestor', 'Gestor Financeiro', 'Gestor Auditor',
            'Gestor Despachante', 'Transitário', 'Praticante', 'Operador',
            'Customer Manager',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Administrador — todas as permissões
        Role::findByName('Administrador', 'web')->syncPermissions(Permission::all());

        // Gestor — gestão da empresa (SEM permissions.manage / system.configure)
        Role::findByName('Gestor', 'web')->syncPermissions([
            'users.view', 'users.create', 'users.update',
            'customers.view', 'customers.create', 'customers.update', 'customers.delete',
            'customers.activate', 'customers.deactivate',
            'customers.manage_portal_credentials',
            'customers.view_processos', 'customers.view_licenciamentos',
            'customers.view_documents', 'customers.associate_empresa',
            'processos.view', 'processos.create', 'processos.update', 'processos.delete',
            'processos.manage_mercadorias',
            'licenciamentos.view', 'licenciamentos.create', 'licenciamentos.update',
            'licenciamentos.delete', 'licenciamentos.manage_mercadorias',
            'mercadorias.view', 'mercadorias.create', 'mercadorias.update', 'mercadorias.delete',
            'documents.view', 'documents.create', 'documents.update', 'documents.download',
            'invoices.view', 'invoices.create',
            'payments.view', 'receipts.view',
            'reports.view', 'menus.manage',
        ]);

        // Gestor Financeiro
        Role::findByName('Gestor Financeiro', 'web')->syncPermissions([
            'customers.view',
            'documents.view', 'documents.download',
            'invoices.view', 'invoices.create', 'invoices.update',
            'payments.view', 'payments.create', 'payments.update',
            'receipts.view', 'reports.view',
        ]);

        // Gestor Auditor
        Role::findByName('Gestor Auditor', 'web')->syncPermissions([
            'users.view', 'customers.view',
            'processos.view', 'licenciamentos.view', 'mercadorias.view',
            'documents.view', 'documents.download',
            'invoices.view', 'payments.view', 'receipts.view',
            'reports.view', 'audit.view',
        ]);

        // Gestor Despachante
        Role::findByName('Gestor Despachante', 'web')->syncPermissions([
            'customers.view', 'customers.create', 'customers.update',
            'processos.view', 'processos.create', 'processos.update', 'processos.delete',
            'processos.manage_mercadorias',
            'licenciamentos.view', 'licenciamentos.create', 'licenciamentos.update',
            'licenciamentos.delete', 'licenciamentos.manage_mercadorias',
            'mercadorias.view', 'mercadorias.create', 'mercadorias.update', 'mercadorias.delete',
            'documents.view', 'documents.create', 'documents.update', 'documents.download',
        ]);

        // Transitário
        Role::findByName('Transitário', 'web')->syncPermissions([
            'customers.view',
            'processos.view', 'processos.create', 'processos.update',
            'licenciamentos.view',
            'mercadorias.view', 'mercadorias.create', 'mercadorias.update',
            'documents.view', 'documents.create', 'documents.download',
        ]);

        // Praticante
        Role::findByName('Praticante', 'web')->syncPermissions([
            'customers.view',
            'processos.view', 'processos.create', 'processos.update',
            'licenciamentos.view', 'mercadorias.view',
            'documents.view', 'documents.create', 'documents.download',
        ]);

        // Operador
        Role::findByName('Operador', 'web')->syncPermissions([
            'customers.view', 'processos.view', 'licenciamentos.view',
            'mercadorias.view', 'documents.view', 'documents.download',
        ]);

        // Customer Manager
        Role::findByName('Customer Manager', 'web')->syncPermissions([
            'customers.view', 'customers.create', 'customers.update', 'customers.delete',
            'customers.activate', 'customers.deactivate',
            'customers.manage_portal_credentials',
            'customers.view_processos', 'customers.view_licenciamentos',
            'customers.view_documents', 'customers.associate_empresa',
            'processos.view', 'licenciamentos.view', 'mercadorias.view',
            'documents.view', 'documents.download',
            'invoices.view', 'payments.view', 'receipts.view',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}