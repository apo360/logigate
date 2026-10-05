<?php

namespace Tests\Feature;

use App\Domains\Empresa\Actions\CriarEmpresaAction;
use App\Domains\Empresa\Data\EmpresaData;
use App\Domains\Usuarios\Actions\{AtualizarRoleAction, CriarRoleAction, CriarUsuarioEmpresaAction, SincronizarRolesUsuarioAction, SincronizarPermissoesUsuarioAction};
use App\Domains\Usuarios\Data\UsuarioEmpresaData;
use App\Models\{Empresa, User};
use App\Support\{CompanyRbac, TenantContext};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\{DB, Gate};
use Livewire\Livewire;
use Spatie\Permission\Models\{Permission, Role};
use Tests\TestCase;

class CompanyScopedRbacTest extends TestCase
{
    use \Tests\Feature\Processo\ProcessoTestFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertTrue(app()->environment('testing'));
        self::assertSame(getenv('V1_TEST_DATABASE') ?: 'logigate_testing', DB::connection()->getDatabaseName());
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function matrix(string $aRole = 'Administrador', string $bRole = 'Administrador'): array
    {
        [$u, $a] = $this->createTenant('S2-A');
        [$t, $b] = $this->createTenant('S2-B');
        [$foreign, $c] = $this->createTenant('S2-C');
        $u->empresas()->attach($b->id);
        $t->empresas()->attach($a->id);
        foreach ([$a, $b] as $e) {
            CompanyRbac::within((int) $e->id, function () use ($u, $t, $e, $a, $aRole, $bRole): void {
                foreach (['Administrador', 'Gestor', 'Operador'] as $name) {
                    Role::query()->create(['name' => $name, 'guard_name' => 'web', 'empresa_id' => $e->id]);
                }
                $u->assignRole(Role::where('empresa_id', $e->id)->where('name', $e->is($a) ? $aRole : $bRole)->firstOrFail());
                $t->assignRole(Role::where('empresa_id', $e->id)->where('name', 'Operador')->firstOrFail());
                $u->givePermissionTo(['users.create', 'users.update', 'processos.view', 'processos.update', 'empresas.create']);
                $t->givePermissionTo('processos.view');
            });
        }
        $this->actingAs($u)->withSession(['empresa_id' => $a->id]);
        return compact('u', 't', 'a', 'b', 'c', 'foreign');
    }

    private function denied(callable $callback): void
    {
        try {
            $callback();
            self::fail('Expected authorization denial.');
        } catch (AuthorizationException|\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            self::assertTrue($e instanceof AuthorizationException || $e->getStatusCode() === 403);
        }
    }

    private function grants(User $user, Empresa $empresa): array
    {
        $result = [];
        foreach (['model_has_roles', 'model_has_permissions'] as $table) {
            $result[$table] = DB::table($table)->where('model_type', User::class)->where('model_id', $user->id)
                ->where('empresa_id', $empresa->id)->orderBy($table === 'model_has_roles' ? 'role_id' : 'permission_id')->get()->map(fn ($row) => (array) $row)->all();
        }
        return $result;
    }

    public function test_administrator_of_two_companies_cannot_manage_b_from_a(): void
    {
        $m = $this->matrix(); extract($m);
        self::assertTrue($u->hasRole('Administrador'));
        self::assertSame((int) $a->id, getPermissionsTeamId());
        self::assertTrue(Gate::allows('manageUser', [$a, $t]));
        self::assertFalse(Gate::allows('manageUser', [$b, $t]));
        self::assertFalse(Gate::allows('manageIntegrations', $b));
        $this->denied(fn () => app(SincronizarRolesUsuarioAction::class)->execute($u, $b, $t, ['Gestor']));
        $this->denied(fn () => app(SincronizarPermissoesUsuarioAction::class)->execute($u, $b, $t, []));
        self::assertFalse(Gate::allows('manageGlobalPermissions'));
        TenantContext::setEmpresa($u, $b);
        self::assertTrue($u->hasRole('Administrador'));
        self::assertTrue(Gate::allows('manageUser', [$b, $t]));
        self::assertFalse(Gate::allows('manageUser', [$c, $foreign]));
    }

    public function test_administrator_a_gestor_b_and_gestor_a_operador_b_switch_effective_authority(): void
    {
        $m = $this->matrix('Administrador', 'Gestor'); extract($m);
        self::assertTrue($u->hasRole('Administrador'));
        $u->load('roles', 'permissions');
        TenantContext::setEmpresa($u, $b);
        self::assertFalse($u->hasRole('Administrador'));
        self::assertTrue($u->hasRole('Gestor'));
        TenantContext::setEmpresa($u, $a);
        $u->syncRoles([Role::where('empresa_id', $a->id)->where('name', 'Gestor')->firstOrFail()]);
        TenantContext::setEmpresa($u, $b);
        $u->syncRoles([Role::where('empresa_id', $b->id)->where('name', 'Operador')->firstOrFail()]);
        TenantContext::setEmpresa($u, $a);
        self::assertTrue(Gate::allows('manageUsers', $a));
        TenantContext::setEmpresa($u, $b);
        self::assertFalse(Gate::allows('manageUsers', $b));
    }

    public function test_same_role_name_permissions_and_direct_grants_are_isolated(): void
    {
        $m = $this->matrix('Gestor', 'Gestor'); extract($m);
        $u->syncPermissions([]);
        $ra = Role::where('empresa_id', $a->id)->where('name', 'Gestor')->firstOrFail();
        $rb = Role::where('empresa_id', $b->id)->where('name', 'Gestor')->firstOrFail();
        $ra->syncPermissions(['processos.view', 'processos.update']);
        $rb->syncPermissions(['processos.view']);
        self::assertTrue($u->can('processos.update'));
        $u->givePermissionTo('users.update');
        TenantContext::setEmpresa($u, $b);
        $u->syncPermissions([]);
        self::assertTrue($u->can('processos.view'));
        self::assertFalse($u->can('processos.update'));
        self::assertFalse($u->can('users.update'));
        TenantContext::setEmpresa($u, $a);
        self::assertTrue($u->can('users.update'));
        self::assertTrue($u->can('processos.update'));
    }

    public function test_target_b_grants_survive_roles_and_permissions_sync_from_a(): void
    {
        $m = $this->matrix(); extract($m);
        $before = $this->grants($t, $b);
        $t->load('roles', 'permissions');
        app(SincronizarRolesUsuarioAction::class)->execute($u, $a, $t, ['Gestor']);
        app(SincronizarPermissoesUsuarioAction::class)->execute($u, $a, $t, ['processos.update']);
        self::assertSame($before, $this->grants($t, $b));
        self::assertTrue($t->hasRole('Gestor'));
        self::assertTrue($t->hasPermissionTo('processos.update'));
        TenantContext::setEmpresa($u, $b);
        self::assertTrue($t->hasRole('Operador'));
        self::assertFalse($t->hasRole('Gestor'));
        self::assertTrue($t->hasPermissionTo('processos.view'));
        self::assertFalse($t->hasPermissionTo('processos.update'));
    }

    public function test_foreign_user_role_permission_ids_and_http_company_tampering_are_denied(): void
    {
        $m = $this->matrix(); extract($m);
        $foreignRole = Role::where('empresa_id', $b->id)->where('name', 'Gestor')->firstOrFail();
        $this->denied(fn () => app(SincronizarRolesUsuarioAction::class)->execute($u, $a, $foreign, ['Gestor']));
        $this->denied(fn () => app(SincronizarRolesUsuarioAction::class)->execute($u, $a, $t, [$foreignRole->id]));
        $permission = Permission::where('name', 'payments.view')->firstOrFail();
        CompanyRbac::within((int) $b->id, fn () => $u->givePermissionTo($permission));
        $this->denied(fn () => app(SincronizarPermissoesUsuarioAction::class)->execute($u, $a, $t, [$permission->id]));
        $this->post(route('users.assignRole', $t), ['role' => 'Gestor', 'empresa_id' => $b->id])->assertForbidden();
        $this->get(route('users.showAssignRoleForm', $foreign))->assertForbidden();
        $this->put(route('roles.update', $foreignRole), ['name'=>'Gestor','permissions'=>['processos.view']])->assertForbidden();
        $this->delete(route('roles.destroy', $foreignRole))->assertForbidden();
        $this->get(route('roles.edit', $foreignRole))->assertForbidden();
        $this->post(route('permissions.store'), ['name'=>'invented.platform.power'])->assertForbidden();
    }

    public function test_company_role_crud_keeps_b_permission_set(): void
    {
        $m = $this->matrix(); extract($m);
        $ra = Role::where('empresa_id', $a->id)->where('name', 'Gestor')->firstOrFail();
        $rb = Role::where('empresa_id', $b->id)->where('name', 'Gestor')->firstOrFail();
        $rb->syncPermissions(['processos.view']);
        app(AtualizarRoleAction::class)->execute($u, $ra, 'Gestor', ['processos.update']);
        self::assertSame(['processos.view'], $rb->refresh()->permissions->pluck('name')->all());
        self::assertSame(['processos.update'], $ra->refresh()->permissions->pluck('name')->all());
        $this->denied(fn () => app(AtualizarRoleAction::class)->execute($u, $rb, 'Gestor', []));
        $this->denied(fn () => app(CriarRoleAction::class)->execute($u, 'Invented Super Admin', []));
        $created = app(CriarRoleAction::class)->execute($u, 'Gestor Financeiro', ['processos.view']);
        self::assertSame((int) $a->id, (int) $created->empresa_id);
        self::assertSame(['processos.view'], $created->permissions->pluck('name')->all());
        app(\App\Domains\Usuarios\Actions\ExcluirRoleAction::class)->execute($u, $created);
        self::assertNull(Role::find($created->id));
        self::assertSame(['processos.view'], $rb->refresh()->permissions->pluck('name')->all());
        $admin = Role::where('empresa_id',$a->id)->where('name','Administrador')->firstOrFail();
        $this->denied(fn () => app(AtualizarRoleAction::class)->execute($u, $admin, 'Gestor', []));
    }

    public function test_new_and_existing_users_receive_only_company_a_grants(): void
    {
        $m = $this->matrix(); extract($m);
        $before = $this->grants($t, $b);
        $password = $t->getRawOriginal('password');
        $existing = app(CriarUsuarioEmpresaAction::class)->execute($u, $a,
            new UsuarioEmpresaData('Ignored identity overwrite', $t->email, null, 'Gestor', ['processos.update']));
        self::assertTrue($existing->is($t));
        self::assertSame($password, $existing->getRawOriginal('password'));
        self::assertSame($before, $this->grants($t, $b));
        $new = app(CriarUsuarioEmpresaAction::class)->execute($u, $a,
            new UsuarioEmpresaData('S2 fixture', 's2-new@example.test', 'fixture-password', 'Operador', ['processos.view']));
        self::assertSame([(int) $a->id], $new->empresas()->pluck('empresas.id')->map(fn ($id) => (int) $id)->all());
        self::assertTrue($new->hasRole('Operador'));
        self::assertEmpty($this->grants($new, $b)['model_has_roles']);
    }

    public function test_associating_existing_b_only_user_preserves_b_and_cannot_demote_a_admin_as_gestor(): void
    {
        $m = $this->matrix(); extract($m);
        $t->empresas()->detach($a->id);
        $before = $this->grants($t, $b);
        app(CriarUsuarioEmpresaAction::class)->execute($u, $a,
            new UsuarioEmpresaData('Ignored', $t->email, null, 'Operador'));
        self::assertTrue(TenantContext::userBelongsToEmpresa($t, (int) $a->id));
        self::assertSame($before, $this->grants($t, $b));
        $t->syncRoles([Role::where('empresa_id', $a->id)->where('name', 'Administrador')->firstOrFail()]);
        $u->syncRoles([Role::where('empresa_id', $a->id)->where('name', 'Gestor')->firstOrFail()]);
        $this->denied(fn () => app(CriarUsuarioEmpresaAction::class)->execute($u, $a,
            new UsuarioEmpresaData('Ignored', $t->email, null, 'Gestor')));
    }

    public function test_gestor_can_manage_target_who_is_admin_only_in_b(): void
    {
        $m = $this->matrix('Gestor', 'Operador'); extract($m);
        CompanyRbac::within((int) $b->id, fn () => $t->syncRoles([
            Role::where('empresa_id', $b->id)->where('name', 'Administrador')->firstOrFail(),
        ]));
        self::assertTrue(Gate::allows('manageUser', [$a, $t]));
        app(SincronizarRolesUsuarioAction::class)->execute($u, $a, $t, ['Gestor']);
        TenantContext::setEmpresa($u, $b);
        self::assertTrue($t->hasRole('Administrador'));
    }

    public function test_company_rbac_and_ownership_both_apply_to_real_process_http_and_policy(): void
    {
        $m = $this->matrix(); extract($m);
        [$estancia, $tipo] = $this->createLookupData();
        foreach ([$a, $b] as $e) {
            $customer = $this->createCustomer($e, $u, 'S2-'.$e->id);
            $exportador = $this->createExportador($e, $u, 'S2-'.$e->id);
            $resources[$e->id] = $this->createProcesso($e, $u, $customer, $exportador, $estancia, $tipo);
        }
        self::assertTrue(Gate::allows('update', $resources[$a->id]));
        self::assertFalse(Gate::allows('update', $resources[$b->id]));
        $this->get(route('processos.show', $resources[$a->id]))->assertOk();
        $this->get(route('processos.show', $resources[$b->id]))->assertNotFound();
        TenantContext::setEmpresa($u, $b);
        $u->syncPermissions(['processos.view']);
        self::assertFalse(Gate::allows('update', $resources[$b->id]));
        self::assertTrue(Gate::allows('view', $resources[$b->id]));
        self::assertFalse(Gate::allows('finalize', $resources[$b->id])); // Unknown catalog capability denies.
        $this->get(route('processos.show', $resources[$a->id]))->assertNotFound();
    }

    private function companyData(string $suffix): EmpresaData
    {
        return EmpresaData::fromArray(['Empresa'=>'S2 '.$suffix, 'NIF'=>'S2-'.$suffix, 'Cedula'=>'S2-'.$suffix,
            'ActividadeComercial'=>'Servicos', 'Designacao'=>'Outro', 'Provincia'=>'Luanda', 'Cidade'=>'Luanda',
            'Email'=>'s2@example.test', 'Contacto_movel'=>'900000000', 'Contacto_fixo'=>'222000000', 'Sigla'=>'S2']);
    }

    public function test_company_creation_assigns_creator_membership_and_admin_atomically(): void
    {
        $m = $this->matrix(); extract($m);
        $before = $this->grants($u, $b);
        $created = app(CriarEmpresaAction::class)->execute($this->companyData('success'));
        self::assertTrue(TenantContext::userBelongsToEmpresa($u, (int) $created->id));
        self::assertSame($before, $this->grants($u, $b));
        self::assertSame((int) $a->id, getPermissionsTeamId());
        TenantContext::setEmpresa($u, $created);
        self::assertTrue($u->hasRole('Administrador'));
        self::assertTrue(Gate::allows('manageUsers', $created));
    }

    public function test_company_creation_failure_rolls_back_company_membership_and_role(): void
    {
        $m = $this->matrix(); extract($m);
        $counts = [Empresa::count(), DB::table('empresa_users')->count(), Role::count(), DB::table('model_has_roles')->count()];
        $dispatcher = clone Role::getEventDispatcher();
        Role::setEventDispatcher($dispatcher);
        $dispatcher->listen('eloquent.created: '.Role::class, function ($role): void {
            if ($role->name === 'Administrador' && $role->empresa_id) throw new \RuntimeException('Controlled role assignment failure');
        });
        try {
            app(CriarEmpresaAction::class)->execute($this->companyData('rollback'));
            self::fail('Expected controlled failure');
        } catch (\RuntimeException $e) {
            self::assertSame('Controlled role assignment failure', $e->getMessage());
        } finally {
            Role::setEventDispatcher(app('events'));
        }
        self::assertSame($counts, [Empresa::count(), DB::table('empresa_users')->count(), Role::count(), DB::table('model_has_roles')->count()]);
        self::assertSame((int) $a->id, getPermissionsTeamId());
    }

    public function test_public_fortify_registration_creates_only_initial_company_admin(): void
    {
        config(['mail.default' => 'array']);
        $this->post('/register', [
            'name'=>'S2 registration fixture', 'empresa'=>'S2 new registration', 'nif'=>'S2-REGISTRATION',
            'email'=>'s2-registration@example.test', 'plano_id'=>DB::table('planos')->value('id'),
            'modalidade_pagamento'=>'monthly', 'password'=>'fixture-password',
            'password_confirmation'=>'fixture-password', 'terms'=>true,
        ])->assertRedirect();
        $creator = User::where('email','s2-registration@example.test')->firstOrFail();
        $empresa = $creator->empresas()->sole();
        $this->assertAuthenticatedAs($creator);
        self::assertSame((int) $empresa->id, TenantContext::empresaId());
        self::assertTrue($creator->hasRole('Administrador'));
        self::assertFalse($creator->hasPermissionTo('system.configure'));
        self::assertSame(1, DB::table('model_has_roles')->where('model_type',User::class)->where('model_id',$creator->id)->count());
        self::assertSame((int) $empresa->id, (int) DB::table('model_has_roles')->where('model_type',User::class)->where('model_id',$creator->id)->value('empresa_id'));
        self::assertNull($creator->empresas()->sole()->pivot->role);
    }

    public function test_no_active_company_and_legacy_global_grants_fail_closed(): void
    {
        $m = $this->matrix(); extract($m);
        $u->syncRoles([]); $u->syncPermissions([]);
        $legacy = Role::whereNull('empresa_id')->where('name', 'Administrador')->firstOrFail();
        DB::table('model_has_roles')->insert(['role_id'=>$legacy->id,'model_id'=>$u->id,'model_type'=>User::class,'empresa_id'=>0]);
        DB::table('model_has_permissions')->insert(['permission_id'=>Permission::where('name','processos.update')->value('id'),
            'model_id'=>$u->id,'model_type'=>User::class,'empresa_id'=>0]);
        CompanyRbac::forgetUser($u);
        self::assertFalse($u->hasRole('Administrador'));
        self::assertFalse($u->can('processos.update'));
        TenantContext::clear();
        self::assertNull(getPermissionsTeamId());
        self::assertFalse($u->hasRole('Administrador'));
        self::assertFalse($u->can('processos.update'));
        self::assertEmpty($u->getAllPermissions());
        self::assertFalse(Gate::allows('manageUser', [$a, $t]));
    }

    public function test_livewire_reloads_rbac_for_b_and_rejects_old_a_snapshot(): void
    {
        $m = $this->matrix('Administrador','Operador'); extract($m);
        $old = Livewire::test(S2RbacProbe::class)->assertSet('administrator', true);
        TenantContext::setEmpresa($u, $b);
        $old->call('refreshAuthority')->assertForbidden();
        Livewire::test(S2RbacProbe::class)->assertSet('administrator', false)->call('refreshAuthority')->assertSet('administrator', false);
    }
}

class S2RbacProbe extends \Livewire\Component
{
    use \App\Livewire\Concerns\RequiresActiveEmpresa;
    public bool $administrator = false;
    public function mount(): void { $this->refreshAuthority(); }
    public function refreshAuthority(): void { $this->administrator = auth()->user()->hasRole('Administrador'); }
    public function render() { return '<div></div>'; }
}
