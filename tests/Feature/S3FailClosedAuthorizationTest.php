<?php

namespace Tests\Feature;

use App\Application\Mercadoria\Services\MercadoriaTenantAccessService;
use App\Models\{Customer, DocumentoArquivo, Empresa, Mercadoria, Produto, User};
use App\Support\{BusinessAuthorization, CompanyRbac, TenantContext};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\{Auth, DB, Gate};
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\{Permission, Role};
use Tests\TestCase;

class S3FailClosedAuthorizationTest extends TestCase
{
    use \Tests\Feature\Processo\ProcessoTestFixtures;
    use \Tests\Feature\Licenciamento\LicenciamentoTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertTrue(app()->environment('testing'));
        self::assertSame('logigate_testing', config('database.connections.'.config('database.default').'.database'));
        self::assertSame('logigate_testing', DB::connection()->getDatabaseName());
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function matrix(): array
    {
        [$u, $a] = $this->createTenant('S3-A');
        [$t, $b] = $this->createTenant('S3-B');
        $u->empresas()->attach($b->id);
        $t->empresas()->attach($a->id);
        [$estancia, $tipo] = $this->createLookupData();
        $resources = [];
        foreach ([$a, $b] as $empresa) {
            $suffix = 'S3-'.$empresa->id;
            $customer = $this->createCustomer($empresa, $u, $suffix);
            $exportador = $this->createExportador($empresa, $u, $suffix);
            $processo = $this->createProcesso($empresa, $u, $customer, $exportador, $estancia, $tipo);
            $licenciamento = $this->createLicenciamentoFor($empresa, $u, $suffix.'-LIC');
            $produto = (new Produto())->forceFill(['id' => $empresa->id, 'empresa_id' => $empresa->id]);
            $documento = (new DocumentoArquivo())->forceFill(['id' => $empresa->id, 'empresa_id' => $empresa->id]);
            $mercadoria = (new Mercadoria())->forceFill(['Fk_Importacao' => $processo->id]);
            $resources[$empresa->id] = compact('customer', 'exportador', 'processo', 'licenciamento', 'produto', 'documento', 'mercadoria');
            CompanyRbac::within((int) $empresa->id, function () use ($u, $empresa): void {
                $role = Role::query()->create(['name' => 'Administrador', 'guard_name' => 'web', 'empresa_id' => $empresa->id]);
                $u->assignRole($role);
            });
        }
        $this->actingAs($u)->withSession(['empresa_id' => $a->id]);
        return compact('u', 't', 'a', 'b', 'resources');
    }

    private function grant(User $user, Empresa $empresa, array $names): void
    {
        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        CompanyRbac::within((int) $empresa->id, fn () => $user->givePermissionTo($names));
        CompanyRbac::forgetUser($user);
    }

    public static function resources(): array
    {
        return [
            'processo' => ['processo', 'processos', ['view', 'update', 'delete']],
            'licenciamento' => ['licenciamento', 'licenciamentos', ['view', 'update', 'delete']],
            'customer' => ['customer', 'customers', ['view', 'update', 'delete']],
            'exportador' => ['exportador', 'exportadores', ['view', 'update', 'delete']],
            'produto' => ['produto', 'produtos', ['view', 'update', 'delete']],
            'documento' => ['documento', 'documents', ['view', 'download', 'delete']],
            'mercadoria' => ['mercadoria', 'mercadorias', ['view', 'update', 'delete']],
        ];
    }

    #[DataProvider('resources')]
    public function test_resource_matrix_requires_permission_in_active_company(string $name, string $module, array $abilities): void
    {
        extract($this->matrix());
        $own = $resources[$a->id][$name];
        $foreign = $resources[$b->id][$name];
        $policy = Gate::getPolicyFor($own);
        $names = array_map(fn ($ability) => $module.'.'.$ability, $abilities);
        foreach ($abilities as $ability) {
            self::assertFalse($policy->$ability($u, $own), 'Administrator without capability');
            self::assertFalse(Gate::allows($ability, $own));
        }
        $this->grant($u, $b, $names);
        foreach ($abilities as $ability) {
            self::assertFalse($policy->$ability($u, $own), 'B permission cannot authorize A');
        }
        $this->grant($u, $a, $names);
        foreach ($abilities as $ability) {
            self::assertTrue($policy->$ability($u, $own));
            self::assertTrue(Gate::allows($ability, $own));
            self::assertFalse($policy->$ability($u, $foreign));
        }
        TenantContext::setEmpresa($u, $b);
        foreach ($abilities as $ability) {
            self::assertTrue($policy->$ability($u, $foreign));
            self::assertFalse($policy->$ability($u, $own));
        }
        $u->load('empresas', 'roles', 'permissions');
        $u->empresas()->detach($b->id);
        foreach ($abilities as $ability) {
            self::assertFalse($policy->$ability($u, $foreign), 'Revoked membership with cached relations');
        }
        session()->forget('empresa_id');
        foreach ($abilities as $ability) {
            self::assertFalse($policy->$ability($u, $foreign), 'No active context');
        }
    }

    public function test_create_operations_require_scoped_capabilities_and_context(): void
    {
        extract($this->matrix());
        foreach (['processo' => 'processos', 'licenciamento' => 'licenciamentos', 'customer' => 'customers', 'exportador' => 'exportadores', 'produto' => 'produtos'] as $name => $module) {
            $class = $resources[$a->id][$name]::class;
            self::assertFalse(Gate::allows('create', $class));
            $this->grant($u, $b, [$module.'.create']);
            self::assertFalse(Gate::allows('create', $class));
            $this->grant($u, $a, [$module.'.create']);
            self::assertTrue(Gate::allows('create', $class));
        }
        self::assertFalse(Gate::allows('upload', [DocumentoArquivo::class, $a]));
        $this->grant($u, $a, ['documents.create']);
        self::assertTrue(Gate::allows('upload', [DocumentoArquivo::class, $a]));
        self::assertFalse(Gate::allows('upload', [DocumentoArquivo::class, $b]));
        session()->forget('empresa_id');
        foreach (['processo', 'licenciamento', 'customer', 'exportador', 'produto'] as $name) {
            self::assertFalse(Gate::allows('create', $resources[$a->id][$name]::class));
        }
    }

    public function test_unknown_permission_and_permission_exceptions_never_authorize(): void
    {
        extract($this->matrix());
        self::assertFalse(BusinessAuthorization::allows($u, 'licenciamentos.undefined'));
        $mock = \Mockery::mock(User::class)->makePartial();
        $mock->setRawAttributes($u->getAttributes());
        $mock->shouldReceive('empresas')->andReturn($u->empresas());
        $mock->shouldReceive('hasPermissionTo')->andThrow(new \RuntimeException('Permission lookup unavailable'));
        Auth::setUser($mock);
        foreach ($resources[$a->id] as $resource) {
            self::assertFalse(Gate::getPolicyFor($resource)->view($mock, $resource));
        }
        foreach (['create', 'update', 'delete', 'finalize'] as $ability) {
            $policy = Gate::getPolicyFor($resources[$a->id]['processo']);
            self::assertFalse($ability === 'create' ? $policy->create($mock) : $policy->$ability($mock, $resources[$a->id]['processo']));
        }
    }

    private function denied(callable $callback): void
    {
        try {
            $callback();
            self::fail('Expected authorization denial');
        } catch (AuthorizationException $exception) {
            self::assertInstanceOf(AuthorizationException::class, $exception);
        }
    }

    public function test_mercadoria_rejects_missing_permission_foreign_and_conflicting_parents(): void
    {
        extract($this->matrix());
        $service = app(MercadoriaTenantAccessService::class);
        $p = $resources[$a->id]['processo'];
        $mercadoria = $resources[$a->id]['mercadoria'];
        $this->denied(fn () => $service->authorizeContext($u, 'processo', $p->id));
        $this->denied(fn () => $service->authorizeContext($u, 'processo', $p->id, 'mercadorias.undefined'));
        $this->grant($u, $a, ['mercadorias.update']);
        self::assertSame($mercadoria, $service->authorizeMercadoria($u, $mercadoria, 'processo', $p->id, 'mercadorias.update'));
        $mercadoria->licenciamento_id = $resources[$b->id]['licenciamento']->id;
        $this->denied(fn () => $service->authorizeMercadoria($u, $mercadoria, 'processo', $p->id, 'mercadorias.update'));
        self::assertFalse(Gate::allows('update', $mercadoria));
        $this->denied(fn () => $service->authorizeContext($u, 'processo', $resources[$b->id]['processo']->id, 'mercadorias.update'));
        $mercadoria->licenciamento_id = $resources[$a->id]['licenciamento']->id;
        self::assertTrue(Gate::allows('update', $mercadoria));
        $mercadoria->empresa_id = $b->id;
        self::assertFalse(Gate::allows('update', $mercadoria));
        self::assertFalse(Gate::allows('view', new Mercadoria()));
    }

    public function test_shared_customer_access_does_not_authorize_foreign_children(): void
    {
        extract($this->matrix());
        $shared = $resources[$a->id]['customer'];
        $shared->empresas()->attach($b->id);
        $before = DB::table('customers')->where('id', $shared->id)->first();
        $associations = $shared->empresas()->pluck('empresas.id')->all();
        $this->grant($u, $a, ['customers.view', 'processos.view', 'licenciamentos.view', 'documents.view']);
        self::assertTrue(Gate::allows('view', $shared));
        foreach (['processo', 'licenciamento', 'documento'] as $name) {
            self::assertFalse(Gate::allows('view', $resources[$b->id][$name]));
        }
        $u->revokePermissionTo('customers.view');
        self::assertFalse(Gate::allows('view', $shared));
        self::assertFalse(Gate::allows('restore', $shared));
        $this->grant($u, $a, ['customers.update', 'customers.delete']);
        self::assertFalse(Gate::allows('update', $shared));
        self::assertFalse(Gate::allows('delete', $shared));
        $this->denied(fn () => app(\App\Application\Customer\Actions\UpdateCustomerAction::class)->execute(
            new \App\Application\Customer\DTOs\UpdateCustomerDTO($shared->id, ['CompanyName' => 'Tampered'])
        ));
        self::assertEquals($before, DB::table('customers')->where('id', $shared->id)->first());
        self::assertSame($associations, $shared->empresas()->pluck('empresas.id')->all());
    }

    public function test_empresa_and_user_management_need_capability_even_for_administrator(): void
    {
        extract($this->matrix());
        self::assertTrue(Gate::allows('select', $b));
        foreach (['view', 'update', 'delete'] as $ability) {
            self::assertFalse(Gate::allows($ability, $a));
        }
        self::assertFalse(Gate::allows('manageUser', [$a, $t]));
        self::assertFalse(Gate::allows('manageEmpresaPermissions', $a));
        $this->grant($u, $b, ['users.update', 'empresas.view', 'empresas.update', 'empresas.delete']);
        self::assertFalse(Gate::allows('manageUser', [$a, $t]));
        $this->grant($u, $a, ['users.update', 'empresas.view', 'empresas.update', 'empresas.delete']);
        foreach (['view', 'update', 'delete'] as $ability) {
            self::assertTrue(Gate::allows($ability, $a));
            self::assertFalse(Gate::allows($ability, $b));
        }
        self::assertTrue(Gate::allows('manageUser', [$a, $t]));
        self::assertFalse(Gate::allows('manageUser', [$b, $t]));
        self::assertTrue(Gate::allows('manageEmpresaPermissions', $a));
        TenantContext::setEmpresa($u, $b);
        $gestor = Role::query()->create(['empresa_id' => $b->id, 'name' => 'Gestor', 'guard_name' => 'web']);
        $u->syncRoles([$gestor]);
        self::assertFalse($u->hasRole('Administrador'));
        self::assertTrue(Gate::allows('manageUser', [$b, $t]));
        $u->syncPermissions([]);
        self::assertFalse(Gate::allows('manageUser', [$b, $t]));
        self::assertFalse(Gate::allows('manageIntegrations', $b));
        session()->forget('empresa_id');
        self::assertFalse(Gate::allows('manageUser', [$b, $t]));
    }

    public function test_real_http_permission_denials_and_foreign_binding_return_expected_status(): void
    {
        extract($this->matrix());
        (new \ReflectionProperty(app(), 'isRunningInConsole'))->setValue(app(), false);
        foreach (['processo' => 'processos.show', 'licenciamento' => 'licenciamentos.show', 'customer' => 'customers.show'] as $name => $route) {
            $this->get(route($route, $resources[$a->id][$name]->id))->assertForbidden();
            $this->get(route($route, $resources[$b->id][$name]->id))->assertNotFound();
        }
        $this->get(route('exportadors.edit', $resources[$a->id]['exportador']->id))->assertForbidden();
        $this->withSession(['_token' => 's3-http-token'])
            ->delete(route('exportadors.destroy', $resources[$a->id]['exportador']->id), ['_token' => 's3-http-token'])->assertForbidden();
        $this->grant($u, $a, ['processos.view', 'licenciamentos.view', 'customers.view']);
        $this->get(route('processos.show', $resources[$a->id]['processo']->id))->assertOk();
        $this->get(route('customers.show', $resources[$a->id]['customer']->id))->assertOk();
    }

    public function test_legacy_global_permissions_cannot_authorize_any_critical_resource(): void
    {
        extract($this->matrix());
        $legacy = Role::query()->create(['name' => 'Administrador', 'guard_name' => 'web', 'empresa_id' => null]);
        foreach (self::resources() as [$name, $module, $abilities]) {
            foreach ($abilities as $ability) {
                $permission = Permission::firstOrCreate(['name' => $module.'.'.$ability, 'guard_name' => 'web']);
                DB::table('role_has_permissions')->insertOrIgnore(['role_id' => $legacy->id, 'permission_id' => $permission->id]);
                DB::table('model_has_permissions')->insert(['empresa_id' => 0, 'model_type' => User::class, 'model_id' => $u->id, 'permission_id' => $permission->id]);
            }
        }
        DB::table('model_has_roles')->insert(['empresa_id' => 0, 'model_type' => User::class, 'model_id' => $u->id, 'role_id' => $legacy->id]);
        CompanyRbac::forgetUser($u);
        foreach (self::resources() as [$name, $module, $abilities]) {
            foreach ($abilities as $ability) {
                self::assertFalse(Gate::allows($ability, $resources[$a->id][$name]));
                self::assertFalse(BusinessAuthorization::allows($u, $module.'.'.$ability));
            }
        }
    }

    public function test_sensitive_process_and_licenciamento_actions_reauthorize_before_mutation(): void
    {
        extract($this->matrix());
        $processo = $resources[$a->id]['processo'];
        $before = DB::table('processos')->where('id', $processo->id)->first();
        $this->denied(fn () => app(\App\Application\Processo\Actions\AtualizarProcessoAction::class)->execute(
            new \App\Application\Processo\DTOs\AtualizarProcessoDTO(id: $processo->id, descricao: 'Tampered')
        ));
        $this->denied(fn () => app(\App\Application\Processo\Actions\ExcluirProcessoAction::class)->execute($processo->id));
        $this->denied(fn () => app(\App\Application\Processo\Actions\FinalizarProcessoAction::class)->execute($processo->id));
        self::assertEquals($before, DB::table('processos')->where('id', $processo->id)->first());
        $licenciamento = $resources[$a->id]['licenciamento'];
        $this->denied(fn () => app(\App\Application\Licenciamento\Actions\ExcluirLicenciamentoAction::class)->execute($licenciamento->id));
        self::assertTrue(DB::table('licenciamentos')->where('id', $licenciamento->id)->exists());
    }

    public function test_new_catalog_definitions_are_idempotent_and_never_grant_authority(): void
    {
        extract($this->matrix());
        $before = [];
        foreach (['model_has_roles', 'model_has_permissions', 'role_has_permissions'] as $table) {
            $before[$table] = DB::table($table)->count();
        }
        $migration = require database_path('migrations/2026_10_01_000002_add_exportador_produto_permission_catalog.php');
        $migration->up();
        $count = Permission::count();
        $migration->up();
        self::assertSame($count, Permission::count());
        foreach ($before as $table => $expected) {
            self::assertSame($expected, DB::table($table)->count());
        }
        foreach (['exportadores', 'produtos'] as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $operation) {
                self::assertTrue(Permission::where('name', $module.'.'.$operation)->where('guard_name', 'web')->exists());
                self::assertFalse(BusinessAuthorization::allows($u, $module.'.'.$operation));
            }
        }
    }

    public function test_livewire_create_rechecks_permission_after_mount(): void
    {
        extract($this->matrix());
        $this->grant($u, $a, ['exportadores.create']);
        $component = \Livewire\Livewire::actingAs($u)->test(\App\Livewire\Forms\ExportadorQuickForm::class);
        $u->revokePermissionTo('exportadores.create');
        $before = DB::table('exportadors')->count();
        $component->call('save')->assertForbidden();
        self::assertSame($before, DB::table('exportadors')->count());
    }

    public function test_shared_exportador_cannot_be_updated_globally_or_deleted_from_other_empresa(): void
    {
        extract($this->matrix());
        $exportador = $resources[$a->id]['exportador'];
        $exportador->empresas()->attach($b->id);
        $this->grant($u, $a, ['exportadores.view', 'exportadores.update', 'exportadores.delete']);
        self::assertTrue(Gate::allows('update', $exportador));
        self::assertFalse(Gate::allows('updateProfile', $exportador));
        self::assertFalse(Gate::allows('forceDelete', $exportador));
        $this->denied(fn () => app(\App\Domains\Exportadores\Actions\DeleteExportadorAction::class)->execute(
            $resources[$b->id]['exportador'], $a, $u
        ));
        app(\App\Domains\Exportadores\Actions\DeleteExportadorAction::class)->execute($exportador, $a, $u);
        self::assertFalse($exportador->empresas()->where('empresas.id', $a->id)->exists());
        self::assertTrue($exportador->empresas()->where('empresas.id', $b->id)->exists());
        self::assertTrue(DB::table('exportadors')->where('id', $exportador->id)->exists());
        self::assertFalse(Gate::allows('view', $exportador));
    }

    public function test_mercadoria_creation_requires_capability_and_owned_parent(): void
    {
        extract($this->matrix());
        $p = $resources[$a->id]['processo'];
        self::assertFalse(Gate::allows('create', [Mercadoria::class, $p]));
        $this->grant($u, $b, ['mercadorias.create']);
        self::assertFalse(Gate::allows('create', [Mercadoria::class, $p]));
        $this->grant($u, $a, ['mercadorias.create']);
        self::assertTrue(Gate::allows('create', [Mercadoria::class, $p]));
        self::assertFalse(Gate::allows('create', Mercadoria::class));
        self::assertFalse(Gate::allows('create', [Mercadoria::class, $resources[$b->id]['licenciamento']]));
        session()->forget('empresa_id');
        self::assertFalse(Gate::allows('create', [Mercadoria::class, $p]));
    }

    public function test_user_removal_needs_delete_permission_and_preserves_foreign_membership_grants(): void
    {
        extract($this->matrix());
        $this->grant($u, $a, ['users.update']);
        $this->grant($t, $b, ['processos.view']);
        $foreign = DB::table('model_has_permissions')->where('model_id', $t->id)->where('empresa_id', $b->id)->get();
        $action = app(\App\Domains\Usuarios\Actions\RemoverUsuarioDaEmpresaAction::class);
        try {
            $action->execute($u, $a, $t);
            self::fail('users.update cannot authorize deletion');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        self::assertTrue($t->empresas()->where('empresas.id', $a->id)->exists());
        $this->grant($u, $a, ['users.delete']);
        $action->execute($u, $a, $t);
        self::assertFalse($t->empresas()->where('empresas.id', $a->id)->exists());
        self::assertTrue($t->empresas()->where('empresas.id', $b->id)->exists());
        self::assertEquals($foreign, DB::table('model_has_permissions')->where('model_id', $t->id)->where('empresa_id', $b->id)->get());
    }
}
