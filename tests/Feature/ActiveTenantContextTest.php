<?php

namespace Tests\Feature;

use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Models\Scopes\TenantScope;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class ActiveTenantContextTest extends TestCase
{
    use ProcessoTestFixtures;
    use \Tests\Feature\Licenciamento\LicenciamentoTestSupport;

    private bool $transactionStarted = false;

    protected function setUp(): void
    {
        parent::setUp();
        // Check before opening any database connection. No migrations or seeds.
        self::assertTrue(app()->environment('testing'));
        self::assertSame(getenv('V1_TEST_DATABASE') ?: 'logigate_testing', config('database.connections.' . config('database.default') . '.database'));
        DB::beginTransaction();
        $this->transactionStarted = true;
        (new \ReflectionProperty(app(), 'isRunningInConsole'))->setValue(app(), false);
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function multiCompanyUser(): array
    {
        [$user, $a] = $this->createTenant('S1-A');
        [, $b] = $this->createTenant('S1-B');
        [, $c] = $this->createTenant('S1-C');
        $user->empresas()->attach($b->id);
        $this->actingAs($user);
        return [$user, $a, $b, $c];
    }

    public function test_post_switch_accepts_memberships_and_preserves_context_on_rejection(): void
    {
        [$user, $a, $b, $c] = $this->multiCompanyUser();
        $this->withSession(['_token' => 's1-switch-token', 'empresa_id' => $a->id])
            ->post(route('empresa-context.update'), ['_token' => 's1-switch-token', 'empresa_id' => $b->id, 'return_to' => 'https://example.com'])
            ->assertRedirect(route('dashboard'))->assertSessionHas('empresa_id', $b->id);
        self::assertSame((int) $b->id, TenantContext::empresaId($user));
        $this->post(route('empresa-context.update'), ['_token' => session()->token(), 'empresa_id' => $c->id])
            ->assertForbidden()->assertSessionHas('empresa_id', $b->id);
        $this->post(route('empresa-context.update'), ['_token' => session()->token(), 'empresa_id' => $a->id])
            ->assertRedirect(route('dashboard'))->assertSessionHas('empresa_id', $a->id);
    }

    public function test_customer_and_process_bindings_follow_active_company(): void
    {
        [$user, $a, $b] = $this->multiCompanyUser();
        [$estancia, $tipo] = $this->createLookupData();
        $resources = [];
        foreach ([$a, $b] as $empresa) {
            session()->put('empresa_id', $empresa->id);
            $customer = $this->createCustomer($empresa, $user, 'S1-' . $empresa->id);
            $exportador = $this->createExportador($empresa, $user, 'S1-' . $empresa->id);
            $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estancia, $tipo);
            $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'S1-BIND-' . $empresa->id);
            $produtoId = DB::table('produtos')->insertGetId($this->onlyExistingColumns('produtos', [
                'empresa_id' => $empresa->id, 'ProductType' => 'S', 'ProductCode' => 'S1-' . $empresa->id,
                'ProductGroup' => 'S1', 'ProductDescription' => 'Produto S1', 'ProductNumberCode' => 'S1-' . $empresa->id,
                'status' => 0, 'created_at' => now(), 'updated_at' => now(),
            ]));
            $subscricaoId = DB::table('subscricoes')->insertGetId($this->onlyExistingColumns('subscricoes', [
                'empresa_id' => $empresa->id, 'tipo_plano' => 'Teste', 'modalidade_pagamento' => 'monthly',
                'valor_pago' => 0, 'data_subscricao' => now(), 'data_inicio' => now(),
                'data_expiracao' => now()->addMonth(), 'status' => 'ativa',
                'renovacao_automatica' => false, 'created_at' => now(), 'updated_at' => now(),
            ]));
            $resources[(int) $empresa->id] = [
                'customer' => $customer, 'processo' => $processo, 'licenciamento' => $licenciamento,
                'produto' => \App\Models\Produto::withoutGlobalScope(TenantScope::class)->findOrFail($produtoId),
                'subscricao' => \App\Models\Subscricao::withoutGlobalScope(TenantScope::class)->findOrFail($subscricaoId),
            ];
        }
        foreach ([$a, $b, $a] as $active) {
            session()->put('empresa_id', $active->id);
            foreach ($resources as $owner => $models) {
                foreach ($models as $name => $model) {
                    $binding = Route::getBindingCallback($name);
                    if ($owner === (int) $active->id) {
                        self::assertSame((int) $model->id, (int) $binding($model->id)->id);
                    } else {
                        try {
                            $binding($model->id);
                            self::fail('Binding exposed a non-active company');
                        } catch (ModelNotFoundException) {
                            self::assertTrue(true);
                        }
                    }
                }
            }
            // Empresa binding is membership-based so B remains selectable from A.
            self::assertSame((int) $b->id, (int) Route::getBindingCallback('empresa')($b->id)->id);
        }
    }

    public function test_all_operational_bindings_deny_missing_and_invalid_context(): void
    {
        [, , , $c] = $this->multiCompanyUser();
        foreach ([null, $c->id] as $invalid) {
            session()->put('empresa_id', $invalid);
            foreach (['customer', 'processo', 'licenciamento', 'produto', 'subscricao'] as $name) {
                try {
                    Route::getBindingCallback($name)(1);
                    self::fail('Binding accepted invalid context');
                } catch (HttpException $exception) {
                    self::assertSame(404, $exception->getStatusCode());
                }
            }
        }
    }

    public function test_shared_customer_identity_does_not_expand_active_company_ids(): void
    {
        [$user, $a, $b] = $this->multiCompanyUser();
        session()->put('empresa_id', $a->id);
        $customer = $this->createCustomer($a, $user, 'S1-SHARED');
        $customer->empresas()->syncWithoutDetaching([$b->id]);
        foreach ([$a, $b] as $active) {
            session()->put('empresa_id', $active->id);
            $service = app(CustomerTenantAccessService::class);
            self::assertTrue($service->canAccess($user, $customer));
            self::assertSame([(int) $active->id], $service->empresaIds($user)->all());
            self::assertSame((int) $customer->id, (int) Route::getBindingCallback('customer')($customer->id)->id);
        }
    }

    public function test_company_switch_requires_authentication_and_csrf(): void
    {
        $this->withSession([])->post(route('empresa-context.update'), ['_token' => session()->token(), 'empresa_id' => 1])->assertRedirect(route('login'));
        [$user, $a, $b] = $this->multiCompanyUser();
        $this->app->bind(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, S1CsrfMiddleware::class);
        $this->withSession(['empresa_id' => $a->id, '_token' => 's1-csrf-token'])
            ->post(route('empresa-context.update'), ['empresa_id' => $b->id])
            ->assertStatus(419)->assertSessionHas('empresa_id', $a->id);
        $this->post(route('empresa-context.update'), ['empresa_id' => $b->id, '_token' => 's1-csrf-token'])
            ->assertRedirect(route('dashboard'))->assertSessionHas('empresa_id', $b->id);
    }
}

class S1CsrfMiddleware extends \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken
{
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
