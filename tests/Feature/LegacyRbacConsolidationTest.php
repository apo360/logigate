<?php

namespace Tests\Feature;

use App\Application\Empresa\Actions\ConsolidarLegacyRbacAction;
use App\Models\{User, Empresa};
use App\Support\{CompanyRbac, TenantContext};
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\{Role, Permission};
use Tests\TestCase;

class LegacyRbacConsolidationTest extends TestCase
{
    use \Tests\Feature\Processo\ProcessoTestFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertTrue(app()->environment('testing'));
        self::assertSame('logigate_testing', DB::connection()->getDatabaseName());
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function legacyRole(User $user, string $name): Role
    {
        $role = Role::whereNull('empresa_id')->where('guard_name','web')->where('name',$name)->sole();
        DB::table('model_has_roles')->insert(['role_id'=>$role->id,'model_id'=>$user->id,'model_type'=>$user->getMorphClass(),'empresa_id'=>0]);
        return $role;
    }

    private function legacyPermission(User $user, string $name): Permission
    {
        $permission = Permission::where('guard_name','web')->where('name',$name)->sole();
        DB::table('model_has_permissions')->insert(['permission_id'=>$permission->id,'model_id'=>$user->id,'model_type'=>$user->getMorphClass(),'empresa_id'=>0]);
        return $permission;
    }

    private function consolidate(): array
    {
        $counts = app(ConsolidarLegacyRbacAction::class)->execute();
        self::assertSame(0, $counts['Errors']);
        return $counts;
    }

    private function scoped(User $user, Empresa $empresa, callable $callback): mixed
    {
        return CompanyRbac::within((int) $empresa->id, function () use ($user, $callback) {
            CompanyRbac::forgetUser($user);
            return $callback();
        });
    }

    private function snapshot(User $user, int $empresaId): array
    {
        $result = [];
        foreach (['model_has_roles'=>'role_id','model_has_permissions'=>'permission_id'] as $table=>$column) {
            $result[$table] = DB::table($table)->where('model_type',$user->getMorphClass())->where('model_id',$user->id)
                ->where('empresa_id',$empresaId)->orderBy($column)->get()->map(fn ($row)=>(array)$row)->all();
        }
        return $result;
    }

    public function test_single_membership_roles_and_direct_permissions_convert_without_deleting_legacy(): void
    {
        [$u,$a] = $this->createTenant('S21-SINGLE');
        $this->legacyRole($u,'Gestor');
        $this->legacyPermission($u,'processos.update');
        $legacy = $this->snapshot($u,0);
        $this->actingAs($u)->withSession(['empresa_id'=>$a->id]);
        self::assertFalse($u->hasRole('Gestor'));
        self::assertFalse($u->can('processos.update'));
        $counts = $this->consolidate();
        self::assertGreaterThanOrEqual(1,$counts['Team roles migrated']);
        self::assertGreaterThanOrEqual(1,$counts['Direct permissions migrated']);
        self::assertTrue($u->hasRole('Gestor')); // Same authenticated instance; no logout/login.
        self::assertTrue($u->can('processos.update'));
        self::assertSame('Gestor',$u->roleNaEmpresa());
        self::assertSame($legacy,$this->snapshot($u,0));
    }

    public function test_explicit_membership_roles_convert_per_company_and_override_global_inference(): void
    {
        [$u,$a] = $this->createTenant('S21-EXPLICIT-A');
        [, $b] = $this->createTenant('S21-EXPLICIT-B');
        $u->empresas()->attach($b->id,['role'=>'Operador']);
        $u->empresas()->updateExistingPivot($a->id,['role'=>'Gestor']);
        $this->legacyRole($u,'Administrador');
        $this->legacyPermission($u,'processos.update');
        $counts = $this->consolidate();
        self::assertSame(2,$counts['Membership roles migrated']);
        self::assertGreaterThanOrEqual(2,$counts['Ambiguous grants skipped']);
        $this->actingAs($u)->withSession(['empresa_id'=>$a->id]);
        self::assertTrue($u->hasRole('Gestor'));
        self::assertFalse($u->hasRole('Administrador'));
        TenantContext::setEmpresa($u,$b);
        self::assertTrue($u->hasRole('Operador'));
        self::assertFalse($u->hasRole('Gestor'));
        self::assertFalse($u->hasRole('Administrador'));
        self::assertSame('Operador',$u->roleNaEmpresa());
    }

    public function test_ambiguous_multi_company_role_and_direct_permission_remain_inert(): void
    {
        [$u,$a] = $this->createTenant('S21-AMB-A');
        [, $b] = $this->createTenant('S21-AMB-B');
        $u->empresas()->attach($b->id);
        $this->legacyRole($u,'Administrador');
        $this->legacyPermission($u,'processos.update');
        $legacy = $this->snapshot($u,0);
        $counts = $this->consolidate();
        self::assertGreaterThanOrEqual(2,$counts['Ambiguous grants skipped']);
        foreach ([$a,$b] as $e) {
            $this->scoped($u,$e,function () use ($u): void {
                self::assertFalse($u->hasRole('Administrador'));
                self::assertFalse($u->can('processos.update'));
            });
            self::assertSame(['model_has_roles'=>[],'model_has_permissions'=>[]],$this->snapshot($u,(int)$e->id));
        }
        self::assertSame($legacy,$this->snapshot($u,0));
        $this->actingAs($u); TenantContext::clear();
        self::assertFalse($u->hasRole('Administrador'));
        self::assertFalse($u->can('processos.update'));
    }

    public function test_existing_team_rbac_wins_over_membership_and_global_roles_and_permissions(): void
    {
        [$u,$a] = $this->createTenant('S21-WINS');
        $u->empresas()->updateExistingPivot($a->id,['role'=>'Administrador']);
        $this->legacyRole($u,'Gestor');
        $this->legacyPermission($u,'processos.update');
        $this->scoped($u,$a,function () use ($u,$a): void {
            $role = Role::query()->create(['name'=>'Operador','guard_name'=>'web','empresa_id'=>$a->id]);
            $u->assignRole($role);
            $u->givePermissionTo('processos.view');
        });
        $before = $this->snapshot($u,(int)$a->id);
        $counts = $this->consolidate();
        self::assertGreaterThanOrEqual(2,$counts['Conflicts skipped']);
        self::assertSame($before,$this->snapshot($u,(int)$a->id));
        $this->scoped($u,$a,function () use ($u): void {
            self::assertTrue($u->hasRole('Operador'));
            self::assertFalse($u->hasRole('Administrador'));
            self::assertFalse($u->can('processos.update'));
        });
    }

    public function test_explicit_admin_a_does_not_infer_admin_b_and_pivot_is_not_runtime_authority(): void
    {
        [$u,$a] = $this->createTenant('S21-ADMIN-A');
        [, $b] = $this->createTenant('S21-ADMIN-B');
        $u->empresas()->attach($b->id);
        $u->empresas()->updateExistingPivot($a->id,['role'=>'Administrador']);
        $this->legacyRole($u,'Administrador');
        $this->actingAs($u)->withSession(['empresa_id'=>$a->id]);
        self::assertNull($u->roleNaEmpresa());
        self::assertFalse($u->hasRole('Administrador'));
        $this->consolidate();
        self::assertTrue($u->hasRole('Administrador'));
        $u->empresas()->updateExistingPivot($a->id,['role'=>'Operador']);
        self::assertSame('Administrador',$u->roleNaEmpresa());
        TenantContext::setEmpresa($u,$b);
        self::assertFalse($u->hasRole('Administrador'));
        self::assertNull($u->roleNaEmpresa($a)); // No temporary tenant switch through helper.
    }

    public function test_existing_company_role_definition_is_not_expanded_from_legacy_template(): void
    {
        [$u,$a] = $this->createTenant('S21-DEFINITION');
        $this->legacyRole($u,'Gestor');
        $role = Role::query()->create(['name'=>'Gestor','guard_name'=>'web','empresa_id'=>$a->id]);
        $role->syncPermissions(['processos.view']);
        $this->consolidate();
        self::assertSame(['processos.view'],$role->refresh()->permissions->pluck('name')->all());
        $this->scoped($u,$a,function () use ($u): void {
            self::assertTrue($u->hasRole('Gestor'));
            self::assertFalse($u->hasPermissionTo('processos.update'));
        });
    }

    public function test_platform_and_invalid_membership_roles_are_not_converted(): void
    {
        [$u,$a] = $this->createTenant('S21-INVALID');
        $u->empresas()->updateExistingPivot($a->id,['role'=>'master']);
        $this->legacyRole($u,'Administrador');
        $this->legacyPermission($u,'system.configure');
        $counts = $this->consolidate();
        self::assertGreaterThanOrEqual(2,$counts['Conflicts skipped']);
        self::assertSame(['model_has_roles'=>[],'model_has_permissions'=>[]],$this->snapshot($u,(int)$a->id));
    }

    public function test_command_is_idempotent_and_preserves_all_legacy_and_foreign_grants(): void
    {
        [$u,$a] = $this->createTenant('S21-IDEMP-A');
        [, $b] = $this->createTenant('S21-IDEMP-B');
        $this->legacyRole($u,'Gestor'); $this->legacyPermission($u,'processos.update');
        $foreign = DB::table('model_has_roles')->where('empresa_id',$b->id)->get()->map(fn ($r)=>(array)$r)->all();
        $legacy = $this->snapshot($u,0);
        $this->artisan('security:migrate-legacy-rbac')->assertSuccessful();
        $first = $this->snapshot($u,(int)$a->id);
        $roles = Role::count();
        $counts = $this->consolidate();
        self::assertSame(0,$counts['Team roles migrated']);
        self::assertSame(0,$counts['Direct permissions migrated']);
        self::assertGreaterThan(0,$counts['Already migrated']);
        self::assertSame($roles,Role::count());
        self::assertSame($first,$this->snapshot($u,(int)$a->id));
        self::assertSame($legacy,$this->snapshot($u,0));
        self::assertSame($foreign,DB::table('model_has_roles')->where('empresa_id',$b->id)->get()->map(fn ($r)=>(array)$r)->all());
    }

    public function test_failure_rolls_back_all_companies_of_the_user_and_does_not_block_others(): void
    {
        [$u,$a] = $this->createTenant('S21-FAIL-A');
        [, $b] = $this->createTenant('S21-FAIL-B');
        $u->empresas()->updateExistingPivot($a->id,['role'=>'Gestor']);
        $u->empresas()->attach($b->id,['role'=>'Operador']);
        [$good,$c] = $this->createTenant('S21-GOOD'); $this->legacyRole($good,'Gestor');
        $original = Role::getEventDispatcher();
        $events = clone $original; Role::setEventDispatcher($events);
        $events->listen('eloquent.created: '.Role::class,function ($role) use ($b): void {
            if ((int)$role->empresa_id === (int)$b->id) throw new \RuntimeException('Controlled failure');
        });
        try { $counts = app(ConsolidarLegacyRbacAction::class)->execute(); }
        finally { Role::setEventDispatcher($original); }
        self::assertSame(1,$counts['Errors']);
        foreach ([$a,$b] as $e) self::assertSame(['model_has_roles'=>[],'model_has_permissions'=>[]],$this->snapshot($u,(int)$e->id));
        self::assertSame(0,Role::whereIn('empresa_id',[$a->id,$b->id])->count());
        $this->scoped($good,$c,fn () => self::assertTrue($good->hasRole('Gestor')));
    }

    public function test_command_refuses_other_environment_or_database_before_writes(): void
    {
        $roles = Role::count();
        $environment = app('env');
        app()->instance('env','production');
        try { $this->artisan('security:migrate-legacy-rbac')->assertFailed(); }
        finally { app()->instance('env',$environment); }
        $connection = DB::connection(); $database = $connection->getDatabaseName();
        $connection->setDatabaseName('not_authorized');
        try { $this->artisan('security:migrate-legacy-rbac')->assertFailed(); }
        finally { $connection->setDatabaseName($database); }
        self::assertSame($roles,Role::count());
    }

    public function test_new_user_and_company_after_conversion_create_no_new_global_grants(): void
    {
        [$admin,$a] = $this->createTenant('S21-NEW');
        $this->legacyRole($admin,'Administrador');
        $this->consolidate();
        $this->actingAs($admin)->withSession(['empresa_id'=>$a->id]);
        app(\App\Domains\Usuarios\Actions\CriarRoleAction::class)->execute($admin,'Gestor',['processos.view']);
        $new = app(\App\Domains\Usuarios\Actions\CriarUsuarioEmpresaAction::class)->execute($admin,$a,
            new \App\Domains\Usuarios\Data\UsuarioEmpresaData('S21 new fixture','s21-new@example.test','fixture-password','Gestor'));
        self::assertTrue($new->hasRole('Gestor'));
        self::assertSame(['model_has_roles'=>[],'model_has_permissions'=>[]],$this->snapshot($new,0));
        $legacy = $this->snapshot($admin,0);
        $created = app(\App\Domains\Empresa\Actions\CriarEmpresaAction::class)->execute(
            \App\Domains\Empresa\Data\EmpresaData::fromArray(['Empresa'=>'S21 new company','NIF'=>'S21-NEW-COMPANY',
                'Cedula'=>'S21-NEW-COMPANY','ActividadeComercial'=>'Servicos','Designacao'=>'Outro',
                'Provincia'=>'Luanda','Cidade'=>'Luanda','Email'=>'s21-company@example.test',
                'Contacto_movel'=>'900000000','Contacto_fixo'=>'222000000','Sigla'=>'S21']));
        self::assertTrue(TenantContext::userBelongsToEmpresa($admin,(int)$created->id));
        self::assertSame($legacy,$this->snapshot($admin,0));
        TenantContext::setEmpresa($admin,$created);
        self::assertTrue($admin->hasRole('Administrador'));
        self::assertSame((int)$created->id,(int)$admin->roles()->sole()->empresa_id);
    }

    public function test_write_helpers_resolve_company_role_names_and_never_create_unassigned_grants(): void
    {
        [$u,$a] = $this->createTenant('S21-WRITE-A');
        [, $b] = $this->createTenant('S21-WRITE-B');
        $u->empresas()->attach($b->id);
        foreach ([$a,$b] as $e) Role::query()->create(['name'=>'Gestor','guard_name'=>'web','empresa_id'=>$e->id]);
        $this->actingAs($u)->withSession(['empresa_id'=>$a->id]);
        $u->assignRole('Gestor');
        $u->givePermissionTo('processos.update');
        $aGrants = $this->snapshot($u,(int)$a->id);
        // Direct session changes are synchronized before Spatie captures the write pivot.
        session()->put('empresa_id',$b->id);
        $u->assignRole('Gestor'); $u->givePermissionTo('processos.view');
        self::assertSame($aGrants,$this->snapshot($u,(int)$a->id));
        self::assertTrue($u->hasRole('Gestor'));
        self::assertFalse($u->hasPermissionTo('processos.update'));
        $u->removeRole('Gestor'); $u->revokePermissionTo('processos.view');
        self::assertSame($aGrants,$this->snapshot($u,(int)$a->id));
        self::assertSame(['model_has_roles'=>[],'model_has_permissions'=>[]],$this->snapshot($u,0));
        TenantContext::clear();
        try { $u->assignRole('Gestor'); self::fail('Expected missing context denial'); }
        catch (\Illuminate\Auth\Access\AuthorizationException) { self::assertTrue(true); }
        self::assertSame($aGrants,$this->snapshot($u,(int)$a->id));
    }

    public function test_consolidated_multi_admin_and_gestor_profile_switches_without_leakage(): void
    {
        [$u,$a] = $this->createTenant('S21-PROFILE-A');
        [, $b] = $this->createTenant('S21-PROFILE-B');
        [, $c] = $this->createTenant('S21-PROFILE-C');
        $u->empresas()->updateExistingPivot($a->id,['role'=>'Administrador']);
        $u->empresas()->attach($b->id,['role'=>'Administrador']);
        $u->empresas()->attach($c->id,['role'=>'Gestor']);
        $this->legacyRole($u,'Administrador');
        $this->consolidate();
        $this->actingAs($u)->withSession(['empresa_id'=>$a->id]);
        $snapshots=[];
        foreach ([$a,$b,$c] as $empresa) {
            TenantContext::setEmpresa($u,$empresa);
            self::assertSame(! $empresa->is($c),$u->hasRole('Administrador'));
            self::assertSame($empresa->is($c),$u->hasRole('Gestor'));
            $snapshots[$empresa->id]=$this->snapshot($u,(int)$empresa->id);
        }
        $u->empresas()->updateExistingPivot($a->id,['role'=>'Operador']);
        $this->consolidate();
        foreach ([$a,$b,$c] as $empresa) self::assertSame($snapshots[$empresa->id],$this->snapshot($u,(int)$empresa->id));
        self::assertSame((int)$c->id,TenantContext::empresaId());
    }
}
