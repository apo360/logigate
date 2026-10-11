<?php

namespace Tests\Feature;

use App\Models\{ActivatedModule, Menu, Module, Plano, Subscricao};
use App\Services\{EffectiveModuleResolver, ModuloAtivacaoService, ModuleReconciliation, NavigationResolver};
use App\Support\{CompanyRbac, ModuleEntitlements, NavigationTree, TenantContext};
use Carbon\{Carbon, CarbonImmutable};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{DB, Route};
use Spatie\Permission\Models\{Permission, Role};
use Tests\TestCase;

class NavigationIntegrationTest extends TestCase
{
    use DatabaseTransactions;
    use \Tests\Feature\Processo\ProcessoTestFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('V1_TEST_DATABASE')) {
            throw new \RuntimeException('Run tests/run-navigation-isolated.php, never the business connection.');
        }
        config(['cache.default' => 'array', 'session.driver' => 'array', 'navigation.legacy_until' => '2026-11-09 23:59:59']);
        Carbon::setTestNow('2026-10-10 12:00:00');
        CarbonImmutable::setTestNow('2026-10-10 12:00:00');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function contract(int $empresa): array
    {
        $module = Module::create(['module_name' => 'Processos', 'price' => 0]);
        $plan = Plano::create(['nome' => 'Teste', 'descricao' => 'Plano de teste', 'codigo' => 'teste-'.uniqid(), 'preco_mensal' => 0, 'preco_anual' => 0]);
        $plan->modulos()->attach($module->id);
        $sub = Subscricao::create(['empresa_id' => $empresa, 'plano_id' => $plan->id, 'tipo_plano' => 'Teste',
            'modalidade_pagamento' => 'mensal', 'valor_pago' => 0, 'data_subscricao' => now(),
            'data_inicio' => now()->subDays(9), 'data_expiracao' => now()->addDays(21), 'status' => 'ativa']);

        return [$sub, $module];
    }

    private function activation(Subscricao $sub, Module $module, bool $linked = true): ActivatedModule
    {
        return ActivatedModule::create(['empresa_id' => $sub->empresa_id, 'module_id' => $module->id,
            'subscricao_id' => $linked ? $sub->id : null, 'active' => true, 'activation_date' => now()->subDays(8),
            'data_expiracao' => $linked ? $sub->data_expiracao : null]);
    }

    public function test_module_resolver_validity_revocation_and_company_isolation(): void
    {
        [, $a] = $this->createTenant('NAV-A');
        [, $b] = $this->createTenant('NAV-B');
        [$sub, $module] = $this->contract($a->id);
        $activation = $this->activation($sub, $module);
        $resolver = app(EffectiveModuleResolver::class);
        self::assertSame([$module->id], $resolver->ids($a->id));
        self::assertSame([], $resolver->ids($b->id));
        $sub->update(['status' => 'suspensa']);
        self::assertSame([], $resolver->ids($a->id));
        $sub->update(['status' => 'ativa', 'data_inicio' => now()->addDay()]);
        self::assertSame([], $resolver->ids($a->id));
        $sub->update(['data_inicio' => now()->subDay(), 'data_expiracao' => now()]);
        self::assertSame([], $resolver->ids($a->id));
        $sub->update(['data_expiracao' => now()->addDay()]);
        $activation->update(['active' => false]);
        self::assertSame('explicit_revocation', $resolver->resolve($a->id)[$module->id]['reason']);
    }

    public function test_plan_fallback_requires_one_activation_one_contract_and_open_window(): void
    {
        [, $empresa] = $this->createTenant('NAV-C');
        [$sub, $module] = $this->contract($empresa->id);
        $resolver = app(EffectiveModuleResolver::class);
        self::assertSame([], $resolver->ids($empresa->id));
        $this->activation($sub, $module, false);
        self::assertSame('legacy_plan_fallback', $resolver->resolve($empresa->id)[$module->id]['reason']);
        config(['navigation.legacy_until' => '2026-10-09']);
        self::assertSame([], $resolver->ids($empresa->id));
        config(['navigation.legacy_until' => '2026-11-09']);
        $duplicate = $sub->replicate();
        $duplicate->save();
        self::assertSame('ambiguous_legacy', $resolver->resolve($empresa->id)[$module->id]['reason']);
    }

    public function test_linked_extra_does_not_grant_parent_or_descendants(): void
    {
        $at = CarbonImmutable::now();
        $sub = ['id' => 1, 'empresa_id' => 1, 'plano_id' => 1, 'status' => 'ATIVA'];
        $rows = [['empresa_id' => 1, 'module_id' => 9, 'subscricao_id' => 1, 'active' => true]];
        $decisions = ModuleEntitlements::decide(1, [$sub], [1 => [1, 2]], $rows, $at, null);
        self::assertTrue($decisions[9]['allowed']);
        self::assertFalse($decisions[1]['allowed']);
        self::assertFalse($decisions[2]['allowed']);
    }

    public function test_provisioning_is_idempotent_linked_and_preserves_revocation(): void
    {
        [, $empresa] = $this->createTenant('NAV-D');
        [$sub, $module] = $this->contract($empresa->id);
        $service = app(ModuloAtivacaoService::class);
        $service->ativarModulos($sub);
        $service->ativarModulos($sub);
        self::assertSame(1, ActivatedModule::count());
        $row = ActivatedModule::first();
        self::assertSame($sub->id, (int) $row->subscricao_id);
        self::assertTrue($row->data_expiracao->eq($sub->data_expiracao));
        $row->update(['active' => false]);
        $service->ativarModulos($sub);
        self::assertFalse($row->fresh()->active);
        self::assertSame(1, ActivatedModule::count());
        self::assertSame($sub->plano_id, $module->planos()->sole()->id);
    }

    public function test_reconciliation_compare_and_set_and_replay(): void
    {
        [, $empresa] = $this->createTenant('NAV-E');
        [$sub, $module] = $this->contract($empresa->id);
        $row = $this->activation($sub, $module, false);
        $service = app(ModuleReconciliation::class);
        $manifest = $service->preview($empresa->id);
        self::assertCount(1, $manifest['changes']);
        self::assertNull($row->fresh()->subscricao_id);
        self::assertSame(1, $service->apply($manifest));
        self::assertSame(0, $service->apply($manifest));
        self::assertSame($sub->id, (int) $row->fresh()->subscricao_id);
    }

    public function test_reconciliation_rejects_changed_snapshot(): void
    {
        [, $empresa] = $this->createTenant('NAV-F');
        [$sub, $module] = $this->contract($empresa->id);
        $row = $this->activation($sub, $module, false);
        $service = app(ModuleReconciliation::class);
        $manifest = $service->preview($empresa->id);
        $row->update(['active' => false]);
        $this->expectException(\RuntimeException::class);
        $service->apply($manifest);
    }

    public function test_permissions_multiple_roles_direct_grants_and_company_switch(): void
    {
        [$user, $a] = $this->createTenant('NAV-G');
        [, $b] = $this->createTenant('NAV-H');
        $user->empresas()->attach($b->id);
        $this->actingAs($user);
        TenantContext::setEmpresa($user, $a);
        $view = Permission::findOrCreate('processos.view', 'web');
        Permission::findOrCreate('processos.create', 'web');
        $operator = Role::create(['name' => 'Operador', 'guard_name' => 'web', 'empresa_id' => $a->id]);
        $operator->givePermissionTo($view);
        $trainee = Role::create(['name' => 'Praticante', 'guard_name' => 'web', 'empresa_id' => $a->id]);
        $user->assignRole([$operator, $trainee]);
        $user->givePermissionTo('processos.create');
        $module = Module::create(['module_name' => 'Processos', 'price' => 0]);
        $root = Menu::create(['module_id' => $module->id, 'menu_name' => 'Operação', 'slug' => 'operacao', 'icon' => 'fa fa-folder', 'route' => '#', 'order_priority' => 0]);
        $consult = Menu::create(['parent_id' => $root->id, 'module_id' => $module->id, 'menu_name' => 'Consultar', 'slug' => 'consultar', 'icon' => 'fa fa-search', 'route' => 'processos.index', 'order_priority' => 1]);
        Menu::create(['parent_id' => $root->id, 'module_id' => $module->id, 'menu_name' => 'Criar', 'slug' => 'criar', 'icon' => 'fa fa-plus', 'route' => 'processos.create', 'order_priority' => 2]);
        $resolver = app(NavigationResolver::class);
        self::assertCount(2, $resolver->dynamic($user, [$module->id])[0]['children']);
        $user->revokePermissionTo('processos.create');
        self::assertCount(1, $resolver->dynamic($user, [$module->id])[0]['children']);
        TenantContext::setEmpresa($user, $b);
        self::assertSame([], $resolver->dynamic($user, [$module->id]));
        // A legacy role with company 0 is not a global authority.
        DB::table('model_has_roles')->insert(['role_id' => $operator->id, 'model_type' => get_class($user), 'model_id' => $user->id, 'empresa_id' => 0]);
        CompanyRbac::forgetUser($user);
        self::assertSame([], $resolver->dynamic($user, [$module->id]));
        TenantContext::setEmpresa($user, $a);
        $operator->revokePermissionTo($view);
        CompanyRbac::forgetUser($user);
        self::assertSame([], $resolver->dynamic($user, [$module->id]));
    }

    public function test_tree_neutral_parents_cross_group_pauta_and_stable_role_order(): void
    {
        $rows = [
            ['id' => 1, 'parent_id' => null, 'route' => 'forbidden', 'group_code' => 'finance'],
            ['id' => 2, 'parent_id' => 1, 'route' => 'pauta.consultar', 'group_code' => 'operations'],
            ['id' => 3, 'parent_id' => null, 'route' => 'allowed', 'group_code' => 'clients'],
        ];
        $tree = NavigationTree::filter($rows, fn ($node) => $node['id'] !== 1);
        self::assertSame('#', $tree[0]['route']);
        $labels = config('navigation.groups');
        $groups = NavigationTree::groups($tree, $labels, ['Customer Manager', 'Gestor Financeiro']);
        self::assertArrayNotHasKey('finance', $groups);
        self::assertSame(2, $groups['operations']['items'][0]['id']);
        self::assertSame($groups, NavigationTree::groups($tree, $labels, ['Gestor Financeiro', 'Customer Manager']));
        self::assertSame('clients', array_key_first(NavigationTree::groups($tree, $labels, ['Customer Manager'])));
    }

    public function test_destination_module_guard_preserves_recovery_routes(): void
    {
        [$user, $empresa] = $this->createTenant('NAV-I');
        $this->signInTenant($user);
        Route::middleware(['web'])->get('/navigation-test-destination', fn () => 'ok')->name('navigation.test.destination');
        config(['navigation.route_modules' => ['navigation.test.destination' => 'processos']]);
        $this->get('/navigation-test-destination')->assertForbidden();
        [$sub, $module] = $this->contract($empresa->id);
        $this->activation($sub, $module);
        $this->get('/navigation-test-destination')->assertOk();
        $request = \Illuminate\Http\Request::create('/navigation-test-recovery');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => (new \Illuminate\Routing\Route('GET', '/navigation-test-recovery', fn () => 'ok'))->name('billing.plans'));
        self::assertSame('recovery', app(\App\Http\Middleware\EnsureModuleEntitlement::class)->handle($request, fn () => 'recovery'));
    }

    public function test_activation_callback_reloads_and_remains_idempotent(): void
    {
        [, $empresa] = $this->createTenant('NAV-J');
        [$sub, $module] = $this->contract($empresa->id);
        $sub->update(['status' => 'pendente']);
        $this->mock(\App\Http\Controllers\ArquivoController::class)->shouldReceive('createMasterFolder')->once()->with($empresa->id);
        $action = app(\App\Actions\Subscriptions\ActivateSubscriptionAction::class);
        $action->execute($sub);
        $action->execute($sub); // Same stale object as a repeated callback.
        self::assertTrue($sub->fresh()->isAtiva());
        self::assertSame(1, ActivatedModule::count());
        self::assertSame([$module->id], app(EffectiveModuleResolver::class)->ids($empresa->id));
    }

    public function test_livewire_update_rechecks_module_after_revocation(): void
    {
        [$user, $empresa] = $this->createTenant('NAV-L');
        $this->signInTenant($user);
        [$sub, $module] = $this->contract($empresa->id);
        $row = $this->activation($sub, $module);
        \Livewire\Livewire::component('navigation-entitlement-fixture', NavigationEntitlementFixture::class);
        Route::middleware('web')->get('/navigation-livewire-fixture', fn () => \Livewire\Livewire::mount('navigation-entitlement-fixture'))->name('navigation.test.livewire');
        config(['navigation.route_modules' => ['navigation.test.livewire' => 'processos']]);
        $html = $this->get('/navigation-livewire-fixture')->assertOk()->getContent();
        self::assertSame(1, preg_match('/wire:snapshot="([^"]+)"/', $html, $matches));
        $snapshot = html_entity_decode($matches[1], ENT_QUOTES);
        $payload = fn ($snapshot) => ['components' => [['snapshot' => $snapshot, 'updates' => [],
            'calls' => [['path' => '', 'method' => 'increment', 'params' => []]]]]];
        $first = $this->postJson('/livewire/update', $payload($snapshot), ['X-Livewire' => 'true'])->assertOk();
        $snapshot = $first->json('components.0.snapshot');
        self::assertSame(1, json_decode($snapshot, true)['data']['count']);
        $row->update(['active' => false]);
        $this->postJson('/livewire/update', $payload($snapshot), ['X-Livewire' => 'true'])->assertForbidden();
    }

    public function test_builder_validates_and_persists_visual_group_without_changing_module(): void
    {
        [$user, $empresa] = $this->createTenant('NAV-M');
        $this->signInTenant($user);
        $permission = Permission::findOrCreate('menus.manage', 'web');
        // Fixture for an existing scoped grant; no change to production grant APIs.
        DB::table('model_has_permissions')->insert(['permission_id' => $permission->id,
            'model_type' => get_class($user), 'model_id' => $user->id, 'empresa_id' => $empresa->id]);
        CompanyRbac::forgetUser($user);
        $module = Module::create(['module_name' => 'Processos', 'price' => 0]);
        $component = \Livewire\Livewire::test(\App\Livewire\MenuBuilder::class)->call('create')
            ->set('menu_name', 'Teste')->set('module_id', $module->id)->set('route', 'processos.index')
            ->set('icon', 'fa fa-folder')->set('group_code', 'not-a-group')->call('save')->assertHasErrors('group_code');
        $component->set('group_code', 'operations')->call('save')->assertHasNoErrors();
        self::assertSame('operations', Menu::sole()->group_code);
        self::assertSame($module->id, (int) Menu::sole()->module_id);
    }

    public function test_reconciliation_ambiguous_historical_origin_and_unknown_dates_are_read_only(): void
    {
        [, $empresa] = $this->createTenant('NAV-K');
        [$sub, $module] = $this->contract($empresa->id);
        $row = $this->activation($sub, $module, false);
        $historical = $sub->replicate();
        $historical->status = 'expirada';
        $historical->save();
        $service = app(ModuleReconciliation::class);
        self::assertSame([], $service->preview($empresa->id)['changes']);
        self::assertCount(1, $service->preview($empresa->id)['review']);
        $historical->forceDelete();
        $sub->update(['data_inicio' => null]);
        self::assertSame([], $service->preview($empresa->id)['changes']);
        self::assertNull($row->fresh()->subscricao_id);
    }

    public function test_known_legacy_expiration_and_cross_company_link_never_grant_access(): void
    {
        $sub = ['id' => 1, 'empresa_id' => 1, 'plano_id' => 1, 'status' => 'ativa'];
        $row = ['empresa_id' => 1, 'module_id' => 1, 'active' => true, 'subscricao_id' => null, 'data_expiracao' => '2026-10-09'];
        $decisions = ModuleEntitlements::decide(1, [$sub], [1 => [1]], [$row], CarbonImmutable::now(), '2026-11-09');
        self::assertFalse($decisions[1]['allowed']);
        self::assertSame('activation_outside_validity', $decisions[1]['reason']);
        $row['subscricao_id'] = 2;
        $other = ['id' => 2, 'empresa_id' => 2, 'plano_id' => 1, 'status' => 'ativa'];
        $decisions = ModuleEntitlements::decide(1, [$sub, $other], [1 => [1]], [$row], CarbonImmutable::now(), '2026-11-09');
        self::assertFalse($decisions[1]['allowed']);
    }
}

class NavigationEntitlementFixture extends \Livewire\Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function render(): string
    {
        return '<div>{{ $count }}</div>';
    }
}
