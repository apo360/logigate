<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CustomerPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $guard = 'web';

            /**
             * Permissões principais do módulo Customer.
             */
            $customerPermissions = [
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                'customers.view',
                'customers.create',
                'customers.update',
                'customers.delete',
                'customers.activate',
                'customers.deactivate',
                'customers.manage_portal_credentials',
                'customers.view_processos',
                'customers.view_licenciamentos',
                'customers.view_documents',
                'customers.associate_empresa',
            ];

            /**
             * Permissões relacionadas que o Customer Show usa para navegar/consultar.
             */
            $relatedPermissions = [
                'processos.view',
                'processos.create',
                'processos.update',
                'processos.delete',

                'licenciamentos.view',
                'licenciamentos.create',
                'licenciamentos.update',
                'licenciamentos.delete',

                'documents.view',
                'documents.create',
                'documents.download',

                'invoices.view',
                'invoices.create',

                'payments.view',
                'receipts.view',
            ];

            $permissions = array_values(array_unique([
                ...$customerPermissions,
                ...$relatedPermissions,
            ]));

            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]);
            }

            /**
             * Role operacional para utilizadores internos da empresa.
             */
            $role = Role::firstOrCreate([
                'name' => 'customer-manager',
                'guard_name' => $guard,
            ]);

            $role->syncPermissions($permissions);

            /**
             * Dar permissões ao User 1.
             */
            $user = User::query()->find(1);

            if (!$user) {
                $this->command?->error('User 1 não encontrado.');
                return;
            }

            /**
             * Garante que o utilizador usa o mesmo guard.
             */
            $user->assignRole($role);

            /**
             * Também dá permissões directas ao user.
             * Isto ajuda caso alguma verificação use $user->can()
             * directamente sem depender apenas da role.
             */
            $user->givePermissionTo($permissions);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->command?->info('Permissões Customer criadas e atribuídas ao User 201 com sucesso.');
            $this->command?->info('Role atribuída: customer-manager');
        });
    }
}