<?php

namespace Tests\Unit;

use App\Application\Arquivo\Policies\DocumentoPolicy;
use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Application\Licenciamento\Services\LicenciamentoTenantAccessService;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Models\Customer;
use App\Models\DocumentoArquivo;
use App\Models\Empresa;
use App\Models\Licenciamento;
use App\Models\Processo;
use App\Models\Produto;
use App\Models\User;
use App\Policies\ProdutoPolicy;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use Mockery;
use PHPUnit\Framework\TestCase;

/** No application bootstrap, network or database access. */
class ActiveTenantContextTest extends TestCase
{
    private Store $session;
    private mixed $authenticated;
    private User $user;
    private array $memberships;

    protected function setUp(): void
    {
        parent::setUp();
        $app = new \Illuminate\Foundation\Application(dirname(__DIR__, 2));
        Container::setInstance($app);
        Facade::setFacadeApplication($app);
        $app->instance('config', new Repository(['audit' => ['enabled' => false]]));
        $team = null;
        $registrar = Mockery::mock(\Spatie\Permission\PermissionRegistrar::class);
        $registrar->shouldReceive('getPermissionsTeamId')->andReturnUsing(function () use (&$team) { return $team; });
        $registrar->shouldReceive('setPermissionsTeamId')->andReturnUsing(function ($id) use (&$team) { $team = $id; });
        $registrar->shouldReceive('forgetWildcardPermissionIndex')->andReturnNull();
        $app->instance(\Spatie\Permission\PermissionRegistrar::class, $registrar);
        $cache = Mockery::mock();
        $cache->shouldReceive('forget')->andReturn(true);
        \Illuminate\Support\Facades\Cache::swap($cache);
        $this->session = new Store('active-tenant-test', new ArraySessionHandler(120));
        $this->session->start();
        $app->instance('session', $this->session);
        $this->memberships = [11, 22];
        $this->user = Mockery::mock(User::class)->makePartial();
        $this->user->forceFill(['id' => 7, 'empresa_id' => 33]);
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('where')->with('empresas.id', Mockery::any())->andReturnUsing(function ($column, $id) {
            $query = Mockery::mock(BelongsToMany::class);
            $query->shouldReceive('exists')->andReturn(in_array((int) $id, $this->memberships, true));
            $empresa = new Empresa();
            $empresa->forceFill(['id' => $id]);
            $query->shouldReceive('first')->andReturn($empresa);
            return $query;
        });
        $this->user->shouldReceive('empresas')->andReturn($relation);
        $relation->shouldReceive('limit')->with(2)->andReturnSelf();
        $relation->shouldReceive('get')->andReturnUsing(fn () => new \Illuminate\Database\Eloquent\Collection(
            array_map(fn ($id) => (new Empresa())->forceFill(['id' => $id]), $this->memberships)
        ));
        $this->user->shouldReceive('getAllPermissions')->andReturn(collect([(object) ['name' => 'arquivo.manage']]));
        $this->user->shouldReceive('hasPermissionTo')->andReturnUsing(fn ($name) => in_array($name, ['arquivo.manage', 'produtos.view', 'empresas.view', 'empresas.update', 'empresas.delete'], true));
        $this->authenticated = $this->user;
        $auth = Mockery::mock();
        $auth->shouldReceive('user')->andReturnUsing(fn () => $this->authenticated);
        Auth::swap($auth);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
        parent::tearDown();
    }

    public function test_missing_invalid_and_legacy_contexts_never_fall_back(): void
    {
        $this->session->put(['empresa_atual_id' => 11, 'current_empresa_id' => 22, 'empresa.id' => 11]);
        foreach ([null, 33, 0, -1, '11abc', '11.0', [], true] as $value) {
            $this->session->put('empresa_id', $value);
            self::assertNull(TenantContext::empresaId());
            self::assertNull($this->user->empresaAtiva());
        }
    }

    public function test_company_switch_changes_all_tenant_decisions_in_both_directions(): void
    {
        $processos = new ProcessoTenantAccessService();
        $licenciamentos = new LicenciamentoTenantAccessService();
        $produtos = new ProdutoPolicy();
        $documentos = new DocumentoPolicy();
        foreach ([11, 22, 11] as $active) {
            $empresa = new Empresa();
            $empresa->forceFill(['id' => $active]);
            TenantContext::setEmpresa($this->user, $empresa);
            self::assertSame($active, TenantContext::empresaId($this->user));
            self::assertSame($active, (int) $this->user->empresaAtiva()->id);
            foreach ([11, 22, 33] as $owner) {
                $expected = $active === $owner;
                $processo = (new Processo())->forceFill(['empresa_id' => $owner]);
                $licenciamento = (new Licenciamento())->forceFill(['empresa_id' => $owner]);
                $produto = (new Produto())->forceFill(['empresa_id' => $owner]);
                $documento = (new DocumentoArquivo())->forceFill(['empresa_id' => $owner]);
                self::assertSame($expected, $processos->canAccess($this->user, $processo));
                self::assertSame($expected, $licenciamentos->canAccess($this->user, $licenciamento));
                self::assertSame($expected, $produtos->view($this->user, $produto));
                self::assertSame($expected, $documentos->view($this->user, $documento));
                self::assertSame($expected, $documentos->download($this->user, $documento));
            }
        }
    }

    public function test_invalid_switch_preserves_previous_context_and_session(): void
    {
        $this->session->put('empresa_id', 11);
        $sessionId = $this->session->getId();
        try {
            TenantContext::setEmpresa($this->user, (new Empresa())->forceFill(['id' => 33]));
            self::fail('Foreign membership accepted');
        } catch (AuthorizationException) {
            self::assertSame(11, TenantContext::empresaId());
            self::assertSame($sessionId, $this->session->getId());
        }
    }

    public function test_membership_revocation_and_actor_mismatch_fail_closed(): void
    {
        $this->session->put('empresa_id', 11);
        $other = (new User())->forceFill(['id' => 8]);
        self::assertNull(TenantContext::empresaId($other));
        $this->memberships = [22];
        self::assertNull(TenantContext::empresaId());
        $this->authenticated = null;
        self::assertNull(TenantContext::empresaId());
        $this->authenticated = new \App\Models\ClientePortal();
        self::assertNull(TenantContext::empresaId());
    }

    public function test_customer_identity_can_be_shared_but_operational_ids_are_active_only(): void
    {
        $shared = Mockery::mock(Customer::class)->makePartial();
        $shared->forceFill(['empresa_id' => 11]);
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('where')->with('empresas.id', 22)->andReturnSelf();
        $relation->shouldReceive('exists')->andReturn(true);
        $shared->shouldReceive('empresas')->andReturn($relation);
        $service = new CustomerTenantAccessService();
        foreach ([11, 22] as $active) {
            $this->session->put('empresa_id', $active);
            self::assertTrue($service->canAccess($this->user, $shared));
            self::assertSame([$active], $service->empresaIds($this->user)->all());
        }
        $this->session->forget('empresa_id');
        self::assertFalse($service->canAccess($this->user, $shared));
        self::assertSame([], $service->empresaIds($this->user)->all());
    }

    public function test_successful_switch_rotates_session_and_clears_legacy_values(): void
    {
        $oldId = $this->session->getId();
        $this->session->put(['empresa_atual_id' => 11, 'current_empresa_id' => 11, 'empresa.id' => 11]);
        TenantContext::setEmpresa($this->user, (new Empresa())->forceFill(['id' => 22]));
        self::assertNotSame($oldId, $this->session->getId());
        foreach (['empresa_atual_id', 'current_empresa_id', 'empresa.id'] as $key) {
            self::assertNull($this->session->get($key));
        }
        TenantContext::clear();
        self::assertNull(TenantContext::empresaId());
    }

    public function test_customer_admin_status_does_not_bypass_active_tenant(): void
    {
        $this->user->shouldReceive('hasRole')->andReturn(true);
        $this->session->put('empresa_id', 11);
        $customer = Mockery::mock(Customer::class)->makePartial();
        $customer->forceFill(['empresa_id' => 22]);
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('where')->with('empresas.id', 11)->andReturnSelf();
        $relation->shouldReceive('exists')->andReturn(false);
        $customer->shouldReceive('empresas')->andReturn($relation);
        self::assertFalse((new CustomerTenantAccessService())->canAccess($this->user, $customer));
    }

    public function test_empresa_is_selectable_by_membership_but_mutations_require_active_company(): void
    {
        $this->user->shouldReceive('hasRole')->with('Administrador')->andReturn(true);
        $this->user->shouldReceive('can')->with('empresas.delete')->andReturn(true);
        $this->session->put('empresa_id', 11);
        $policy = new \App\Domains\Empresa\Policies\EmpresaPolicy();
        $b = (new Empresa())->forceFill(['id' => 22]);
        self::assertTrue($policy->select($this->user, $b));
        self::assertFalse($policy->view($this->user, $b));
        self::assertFalse($policy->update($this->user, $b));
        self::assertFalse($policy->delete($this->user, $b));
        $this->session->put('empresa_id', 22);
        self::assertTrue($policy->view($this->user, $b));
        self::assertTrue($policy->update($this->user, $b));
        self::assertTrue($policy->delete($this->user, $b));
    }

    public function test_portal_document_policy_uses_its_own_company_and_customer(): void
    {
        $portal = (new \App\Models\ClientePortal())->forceFill(['is_active' => true, 'empresa_id' => 22, 'customer_id' => 9]);
        $documento = (new DocumentoArquivo())->forceFill([
            'empresa_id' => 22, 'customer_id' => 9,
            'documentable_type' => Customer::class, 'documentable_id' => 9,
            'visibilidade' => 'portal',
        ]);
        $this->authenticated = $portal;
        $this->session->put('empresa_id', 11);
        $policy = new DocumentoPolicy();
        self::assertNull(TenantContext::empresaId());
        self::assertTrue($policy->viewPortal($portal, $documento));
        self::assertTrue($policy->downloadPortal($portal, $documento));
        $documento->empresa_id = 11;
        self::assertFalse($policy->viewPortal($portal, $documento));
    }

    public function test_http_tenant_scope_denies_missing_context_and_restricts_active_company(): void
    {
        (new \ReflectionProperty(app(), 'isRunningInConsole'))->setValue(app(), false);
        $schema = Mockery::mock();
        $schema->shouldReceive('hasColumn')->with('s1_tenant_records', 'empresa_id')->andReturn(true);
        \Illuminate\Support\Facades\Schema::swap($schema);
        $scope = new \App\Models\Scopes\TenantScope();
        $model = new S1TenantRecord();
        $missing = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $missing->shouldReceive('whereRaw')->once()->with('1 = 0')->andReturnSelf();
        $scope->apply($missing, $model);
        $this->session->put('empresa_id', 22);
        $active = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $active->shouldReceive('where')->once()->with('s1_tenant_records.empresa_id', 22)->andReturnSelf();
        $scope->apply($active, $model);
        (new \ReflectionProperty(app(), 'isRunningInConsole'))->setValue(app(), true);
        $console = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $scope->apply($console, $model); // No tenant constraint in the preserved console bypass.
        self::assertTrue(true);
    }

    public function test_authentication_initialization_selects_only_a_unique_membership(): void
    {
        $action = new \App\Application\Empresa\Actions\EstabelecerEmpresaAposAutenticacaoAction();
        self::assertNull($action->execute($this->user));
        self::assertNull(TenantContext::empresaId());
        $this->memberships = [];
        self::assertNull($action->execute($this->user));
        $this->memberships = [22];
        self::assertSame(22, (int) $action->execute($this->user)->id);
        self::assertSame(22, TenantContext::empresaId());
        $other = (new User())->forceFill(['id' => 8]);
        self::assertNull($action->execute($other));
        self::assertSame(22, TenantContext::empresaId());
    }

    public function test_livewire_snapshot_is_rejected_after_company_switch(): void
    {
        $component = new S1TenantComponent();
        $this->session->put('empresa_id', 11);
        $component->mountRequiresActiveEmpresa();
        $component->hydrateRequiresActiveEmpresa();
        self::assertSame(11, $component->activeEmpresaSnapshot);
        $this->session->put('empresa_id', 22);
        try {
            $component->hydrateRequiresActiveEmpresa();
            self::fail('Stale tenant snapshot accepted');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_creating_hook_sets_active_owner_and_rejects_foreign_owner(): void
    {
        (new \ReflectionProperty(app(), 'isRunningInConsole'))->setValue(app(), false);
        \Illuminate\Database\Eloquent\Model::setEventDispatcher(new \Illuminate\Events\Dispatcher(app()));
        S1CreatingRecord::clearBootedModels();
        $this->session->put('empresa_id', 11);
        $record = new S1CreatingRecord();
        $record->creatingForTest();
        self::assertSame(11, $record->empresa_id);
        $record->empresa_id = 22;
        try {
            $record->creatingForTest();
            self::fail('Foreign ownership accepted');
        } catch (AuthorizationException) {
            self::assertSame(22, $record->empresa_id);
        } finally {
            \Illuminate\Database\Eloquent\Model::unsetEventDispatcher();
        }
    }

    public function test_operational_route_bindings_reject_missing_and_foreign_membership_context(): void
    {
        $bindings = [];
        $router = Mockery::mock();
        $router->shouldReceive('bind')->andReturnUsing(function ($name, $callback) use (&$bindings): void {
            $bindings[$name] = $callback;
        });
        \Illuminate\Support\Facades\Route::swap($router);
        $gate = Mockery::mock();
        $gate->shouldReceive('policy')->andReturnSelf();
        $gate->shouldReceive('define')->andReturnSelf();
        \Illuminate\Support\Facades\Gate::swap($gate);
        (new \App\Providers\AppServiceProvider(app()))->boot();
        foreach ([null, 33] as $invalid) {
            $this->session->put('empresa_id', $invalid);
            foreach (['customer', 'processo', 'licenciamento', 'produto', 'subscricao'] as $name) {
                try {
                    $bindings[$name](1);
                    self::fail('Operational binding accepted an invalid tenant');
                } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
                    self::assertSame(404, $exception->getStatusCode());
                }
            }
        }
    }
}

class S1TenantRecord extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 's1_tenant_records';
}

class S1TenantComponent extends \Livewire\Component
{
    use \App\Livewire\Concerns\RequiresActiveEmpresa;
}

class S1CreatingRecord extends S1TenantRecord
{
    use \App\Models\Concerns\BelongsToTenant;

    public function creatingForTest(): void
    {
        $this->fireModelEvent('creating');
    }
}
