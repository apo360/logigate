<?php

namespace Tests\Feature;

use App\Models\{ClientePortal, Customer, DocumentoArquivo, Empresa, Licenciamento, Processo, Produto, User};
use App\Support\TenantContext;
use Illuminate\Support\Facades\{Auth, DB, Gate, Hash, Route};
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class S11RuntimeGateTest extends TestCase
{
    use \Tests\Feature\Processo\ProcessoTestFixtures;
    use \Tests\Feature\Licenciamento\LicenciamentoTestSupport;

    private bool $transaction = false;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertTrue(app()->environment('testing'));
        self::assertSame(getenv('V1_TEST_DATABASE') ?: 'logigate_testing', config('database.connections.'.config('database.default').'.database'));
        DB::beginTransaction();
        $this->transaction = true;
        // Render login notifications in memory; never send mail to real recipients.
        config(['mail.default' => 'array']);
    }

    protected function tearDown(): void
    {
        if ($this->transaction) DB::rollBack();
        parent::tearDown();
    }

    private function httpMode(): void
    {
        (new \ReflectionProperty(app(), 'isRunningInConsole'))->setValue(app(), false);
    }

    private function postWithCsrf(string $url, array $data)
    {
        $this->withSession(['_token' => 's11-runtime-csrf']);
        return $this->post($url, $data + ['_token' => 's11-runtime-csrf']);
    }

    public function test_demo_real_fortify_login_and_single_membership(): void
    {
        $login = getenv('LOGIGATE_DEMO_LOGIN');
        $password = getenv('LOGIGATE_DEMO_PASSWORD');
        if (!$login || !$password) $this->markTestSkipped('Runtime-only demo credentials required.');
        $user = User::where('email', $login)->firstOrFail();
        self::assertSame(1, $user->empresas()->count());
        $empresaId = (int) $user->empresas()->value('empresas.id');
        $this->httpMode();
        $this->withSession(['_token' => 's11-runtime-csrf']);
        $oldId = session()->getId();
        $this->postWithCsrf('/login', ['email' => $login, 'password' => $password])->assertRedirect();
        $this->assertAuthenticatedAs($user, 'web');
        self::assertNotSame($oldId, session()->getId());
        self::assertSame($empresaId, TenantContext::empresaId());
        $this->get(route('dashboard'))->assertOk();
    }

    private function matrix(): array
    {
        [$u, $a] = $this->createTenant('S11-A');
        [, $b] = $this->createTenant('S11-B');
        [, $c] = $this->createTenant('S11-C');
        $u->empresas()->attach($b->id);
        foreach (['processos.view', 'licenciamentos.view', 'customers.view', 'arquivo.manage', 'produtos.view'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            foreach ([$a, $b] as $grantEmpresa) {
                \App\Support\CompanyRbac::within((int) $grantEmpresa->id, fn () => $u->givePermissionTo($name));
            }
        }
        [$estancia, $tipo] = $this->createLookupData();
        $shared = $this->createCustomer($a, $u, 'S11-SHARED');
        $shared->empresas()->syncWithoutDetaching([$b->id]);
        $other = $this->createCustomer($a, $u, 'S11-OTHER');
        $resources = [];
        foreach ([$a, $b] as $e) {
            $customer = $this->createCustomer($e, $u, 'S11-CUST-'.$e->id);
            $exportador = $this->createExportador($e, $u, 'S11-EXP-'.$e->id);
            $processo = $this->createProcesso($e, $u, $shared, $exportador, $estancia, $tipo);
            $licenciamento = $this->createLicenciamentoFor($e, $u, 'S11-LIC-'.$e->id);
            DB::table('licenciamentos')->where('id', $licenciamento->id)->update(['cliente_id' => $shared->id]);
            $licenciamento->cliente_id = $shared->id;
            $produto = Produto::withoutEvents(fn () => Produto::create([
                'empresa_id' => $e->id, 'ProductType' => 'S', 'ProductCode' => 'S11-'.$e->id,
                'ProductGroup' => 'S11', 'ProductDescription' => 'Produto teste', 'ProductNumberCode' => 'S11-'.$e->id,
            ]));
            DB::table('product_prices')->insert($this->onlyExistingColumns('product_prices', [
                'fk_product'=>$produto->id, 'unidade'=>'un', 'custo'=>10, 'venda'=>20, 'venda_sem_iva'=>20,
                'lucro'=>10, 'taxID'=>'0', 'imposto'=>0, 'reasonID'=>0, 'taxAmount'=>0,
                'created_at'=>now(), 'updated_at'=>now(),
            ]));
            $documento = $this->document($e, $shared);
            $resources[$e->id] = compact('customer', 'processo', 'licenciamento', 'produto', 'documento');
        }
        $exportador = $this->createExportador($a, $u, 'S11-EXP-D');
        $pd = $this->createProcesso($a, $u, $other, $exportador, $estancia, $tipo);
        $ld = $this->createLicenciamentoFor($a, $u, 'S11-LIC-D');
        DB::table('licenciamentos')->where('id', $ld->id)->update(['cliente_id' => $other->id]);
        return compact('u', 'a', 'b', 'c', 'shared', 'other', 'resources', 'pd', 'ld');
    }

    private function document(Empresa $empresa, Customer $customer): DocumentoArquivo
    {
        return DocumentoArquivo::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(), 'empresa_id' => $empresa->id,
            'customer_id' => $customer->id, 'documentable_type' => Customer::class,
            'documentable_id' => $customer->id, 'contexto' => 'customer', 'categoria' => 'documentos',
            'visibilidade' => 'portal', 'storage_disk' => 's3', 'storage_key' => 'empresa/'.$empresa->id.'/files/'.$customer->id.'.pdf',
            'nome_original' => 'test.pdf', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size_bytes' => 100,
        ]);
    }

    public function test_real_multi_membership_login_requires_explicit_selection(): void
    {
        $m = $this->matrix(); $u = $m['u'];
        $password = (string) \Illuminate\Support\Str::uuid();
        $u->forceFill(['password' => Hash::make($password)])->save();
        $this->httpMode();
        $this->postWithCsrf('/login', ['email' => $u->email, 'password' => $password])
            ->assertRedirect(route('empresa-context.index'));
        $this->assertAuthenticatedAs($u);
        self::assertNull(TenantContext::empresaId());
        $this->postWithCsrf(route('empresa-context.update'), ['empresa_id' => $m['b']->id])->assertRedirect();
        self::assertSame((int) $m['b']->id, TenantContext::empresaId());
    }

    public function test_http_ab_matrix_global_permissions_shared_children_and_revocation(): void
    {
        $m = $this->matrix(); $u = $m['u'];
        $this->fakeDocumentStorage();
        $this->actingAs($u)->withSession(['empresa_id' => $m['a']->id]);
        self::assertTrue($u->hasPermissionTo('processos.view', 'web'));
        self::assertTrue($u->hasPermissionTo('licenciamentos.view', 'web'));
        self::assertTrue($u->hasPermissionTo('arquivo.manage', 'web'));
        $this->actingAs($u); $this->httpMode();
        foreach ([$m['a'], $m['b']] as $active) {
            $this->postWithCsrf(route('empresa-context.update'), ['empresa_id' => $active->id])->assertRedirect();
            foreach ($m['resources'] as $owner => $r) {
                $same = (int) $owner === (int) $active->id;
                foreach (['processo', 'licenciamento', 'produto', 'customer'] as $name) {
                    $route = ['processo'=>'processos.show','licenciamento'=>'licenciamentos.show','produto'=>'produtos.show','customer'=>'customers.show'][$name];
                    $response = $this->get(route($route, $r[$name]->id));
                    self::assertSame($same ? 200 : 404, $response->status(), $name.' owner='.$owner.' active='.$active->id.' '.($response->exception?->getMessage() ?? ''));
                }
                self::assertSame($same, Gate::allows('view', $r['documento']));
                self::assertSame($same, Gate::allows('download', $r['documento']));
                $download = $this->get(route('documentos-arquivo.download', $r['documento']->id));
                $same ? $download->assertRedirect('https://storage.test/authorized-document') : $download->assertNotFound();
            }
            self::assertTrue(app(\App\Application\Customer\Services\CustomerTenantAccessService::class)->canAccess($u, $m['shared']));
        }
        $this->postWithCsrf(route('empresa-context.update'), ['empresa_id' => $m['c']->id])->assertForbidden();
        self::assertSame((int) $m['b']->id, TenantContext::empresaId());
        $u->load('empresas');
        $u->empresas()->detach($m['b']->id);
        self::assertNull(TenantContext::empresaId());
        $this->get(route('processos.show', $m['resources'][$m['b']->id]['processo']->id))->assertNotFound();
    }

    public function test_livewire_stale_component_and_foreign_create_payload_are_rejected(): void
    {
        $m = $this->matrix(); $this->actingAs($m['u']); $this->httpMode();
        TenantContext::setEmpresa($m['u'], $m['a']);
        $old = Livewire::test(S11TenantProbe::class);
        TenantContext::setEmpresa($m['u'], $m['b']);
        $old->call('run')->assertForbidden();
        Livewire::test(S11TenantProbe::class)->assertSet('activeEmpresaSnapshot', (int) $m['b']->id)->call('run')->assertSet('ran', true);
        $before = DB::table('produtos')->count();
        try {
            Produto::create(['empresa_id' => $m['a']->id, 'ProductType'=>'S','ProductCode'=>'tamper','ProductGroup'=>'S11','ProductDescription'=>'Tamper','ProductNumberCode'=>'tamper']);
            self::fail('Foreign owner persisted');
        } catch (\Illuminate\Auth\Access\AuthorizationException) {
            self::assertSame($before, DB::table('produtos')->count());
        }
    }

    public function test_portal_real_guard_without_saas_context_owns_company_and_customer(): void
    {
        $m = $this->matrix();
        $this->fakeDocumentStorage();
        $password = (string) \Illuminate\Support\Str::uuid();
        $portal = ClientePortal::create(['customer_id'=>$m['shared']->id,'empresa_id'=>$m['a']->id,'username'=>'s11-portal','password'=>Hash::make($password),'is_active'=>true]);
        $docD = $this->document($m['a'], $m['other']);
        $this->httpMode();
        $this->postWithCsrf(route('cliente.portal.login.submit'), ['login'=>$portal->username,'password'=>$password])->assertRedirect(route('cliente.portal.dashboard'));
        $this->assertAuthenticatedAs($portal, 'cliente_portal');
        self::assertNull(session('empresa_id'));
        $a = $m['resources'][$m['a']->id]; $b = $m['resources'][$m['b']->id];
        $this->get(route('cliente.portal.processos.show', $a['processo']->id))->assertRedirect(route('cliente.portal.dashboard'));
        $this->get(route('cliente.portal.processos.show', $b['processo']->id))->assertNotFound();
        $this->get(route('cliente.portal.processos.show', $m['pd']->id))->assertNotFound();
        $this->withSession(['cliente_portal_empresa_id'=>$m['b']->id, 'empresa_id'=>$m['b']->id]);
        $this->get(route('cliente.portal.processos.show', $a['processo']->id))
            ->assertRedirect(route('cliente.portal.dashboard'))->assertSessionHas('cliente_portal_empresa_id', (int) $m['a']->id);
        session()->forget('empresa_id');
        $this->get(route('cliente.portal.licenciamentos.show', $a['licenciamento']->id))->assertOk();
        $this->get(route('cliente.portal.licenciamentos.show', $b['licenciamento']->id))->assertNotFound();
        $this->get(route('cliente.portal.licenciamentos.show', $m['ld']->id))->assertNotFound();
        $dashboard = $this->get(route('cliente.portal.dashboard'))->assertOk();
        $dashboard->assertViewHas('processosCount', 1)->assertViewHas('licenciamentosCount', 1)->assertViewHas('documentosCount', 1);
        self::assertTrue(Gate::forUser($portal)->allows('downloadPortal', $a['documento']));
        self::assertFalse(Gate::forUser($portal)->allows('downloadPortal', $b['documento']));
        self::assertFalse(Gate::forUser($portal)->allows('downloadPortal', $docD));
        $this->get(route('cliente.portal.documentos.download', $a['documento']->id))->assertRedirect('https://storage.test/authorized-document');
        $this->get(route('cliente.portal.documentos.download', $b['documento']->id))->assertNotFound();
        $this->get(route('cliente.portal.documentos.download', $docD->id))->assertNotFound();
        $this->postWithCsrf(route('cliente.portal.empresa-context.update'), ['empresa_id'=>$m['b']->id])->assertForbidden();
        $this->get(route('billing.plans'))->assertRedirect(route('login'));
        self::assertNull(TenantContext::empresaId());
    }

    private function fakeDocumentStorage(): void
    {
        foreach ([\App\Application\Arquivo\Services\ArquivoStorageService::class, \App\Application\Arquivo\Services\FileStorageService::class] as $class) {
            $storage = \Mockery::mock($class)->makePartial();
            $storage->shouldReceive('isConfigured')->andReturn(true);
            $storage->shouldReceive('temporaryUrl')->andReturn('https://storage.test/authorized-document');
            $this->app->instance($class, $storage);
        }
    }
}

class S11TenantProbe extends \Livewire\Component
{
    use \App\Livewire\Concerns\RequiresActiveEmpresa;
    public bool $ran = false;
    public function run(): void { $this->ran = true; }
    public function render() { return '<div>Tenant runtime probe</div>'; }
}
