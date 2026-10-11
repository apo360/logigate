<?php

namespace Tests\Feature\Billing;

use App\Actions\Subscriptions\ActivateSubscriptionAction;
use App\Application\Arquivo\Actions\UploadDocumentoClientePortalAction;
use App\Application\Billing\Contracts\GpoPaymentGateway;
use App\Application\Billing\Contracts\RefPaymentGateway;
use App\Application\Billing\DTOs\PaymentGatewayResult;
use App\Application\Billing\DTOs\StartCheckoutData;
use App\Application\Billing\UseCases\HandleAppyPayWebhook;
use App\Application\Billing\UseCases\ReservePayment;
use App\Application\Billing\UseCases\StartCheckout;
use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Billing\Enums\BillingCycle;
use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\MerchantTransactionId;
use App\Domains\Billing\ValueObjects\PaymentReference;
use App\Http\Controllers\ArquivoController;
use App\Http\Controllers\BillingPlanController;
use App\Http\Middleware\CheckSubscription;
use App\Livewire\CheckoutPayment;
use App\Livewire\SubscriptionWizard;
use App\Models\ClientePortal;
use App\Models\DocumentoArquivo;
use App\Models\Module;
use App\Models\PagamentoOnline;
use App\Models\Plano;
use App\Models\Subscricao;
use App\Models\WebhookEvent;
use App\Services\BillingPlanReview;
use App\Services\BillingQuota;
use App\Services\EffectiveModuleResolver;
use App\Services\StartSubscription;
use App\Services\SubscriptionMaintenance;
use App\Services\SubscriptionQuote;
use App\Support\CompanyRbac;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

/** Acceptance tests replacing the original diagnostic reproductions. */
class BillingMaturityAuditTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('V1_TEST_DATABASE')) {
            throw new \RuntimeException('Use the disposable billing runner.');
        }
        Carbon::setTestNow('2026-10-10 12:00:00');
        Http::preventStrayRequests();
        $this->mock(ArquivoController::class)->shouldReceive('createMasterFolder')->andReturnNull();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function pending(string $suffix = 'BILL'): array
    {
        [$user, $empresa] = $this->createTenant($suffix);
        $this->signInTenant($user);
        CompanyRbac::within($empresa->id, function () use ($user, $empresa) {
            $role = Role::create(['name' => 'Administrador', 'guard_name' => 'web', 'empresa_id' => $empresa->id]);
            $user->assignRole($role);
        });
        $plan = Plano::create(['nome' => 'Pago', 'descricao' => 'Fixture', 'codigo' => 'plan-'.$suffix, 'preco_mensal' => 1000, 'preco_trimestral' => 2800, 'preco_semestral' => 5000, 'preco_anual' => 9000, 'status' => 'activo', 'limite_utilizadores' => 5, 'limite_processos' => 100, 'limite_armazenamento_gb' => 1]);
        $sub = Subscricao::create(['empresa_id' => $empresa->id, 'plano_id' => $plan->id, 'tipo_plano' => 'Pago', 'modalidade_pagamento' => 'monthly', 'valor_pago' => 1000, 'data_subscricao' => now(), 'status' => 'pendente']);

        return [$empresa, $plan, $sub, $user];
    }

    private function payment(Subscricao $sub, string $merchant = 'GPO261010120000'): PagamentoOnline
    {
        return PagamentoOnline::create(['empresa_id' => $sub->empresa_id, 'subscription_id' => $sub->id, 'method' => 'GPO', 'amount' => 1140, 'currency' => 'AOA', 'status' => 'processing', 'merchant_transaction_id' => $merchant, 'idempotency_key' => $merchant]);
    }

    private function payload(PagamentoOnline $payment, string $event = 'audit'): array
    {
        return ['eventId' => $event, 'merchantTransactionId' => $payment->merchant_transaction_id, 'responseStatus' => ['status' => 'Success']];
    }

    public function test_free_choice_cannot_activate_an_unpaid_pending_paid_plan(): void
    {
        [, $paid, $sub] = $this->pending();
        $free = Plano::create(['nome' => 'Gratuito', 'descricao' => 'Fixture', 'codigo' => 'free', 'preco_mensal' => 0, 'preco_trimestral' => 0, 'preco_semestral' => 0, 'preco_anual' => 0, 'status' => 'activo', 'is_free' => true]);
        $this->post(route('billing.start'), ['plano_id' => $free->id, 'modalidade_pagamento' => 'monthly'])->assertSessionHasErrors('plano_id');
        self::assertSame('pendente', $sub->fresh()->status);
        self::assertSame($paid->id, (int) $sub->fresh()->plano_id);
        self::assertSame(0, PagamentoOnline::count());
    }

    public function test_success_webhook_activates_once_without_after_commit_error(): void
    {
        [,,$sub] = $this->pending();
        $payment = $this->payment($sub);
        $payload = $this->payload($payment);
        $service = app(HandleAppyPayWebhook::class);
        self::assertSame('ok', $service->execute($payload, 'audit')->result);
        self::assertSame('paid', $payment->fresh()->status);
        self::assertSame('ativa', $sub->fresh()->status);
        $activated = $sub->fresh()->activated_at;
        Carbon::setTestNow(now()->addDay());
        self::assertSame('duplicate', $service->execute($payload, 'audit')->result);
        self::assertEquals($activated, $sub->fresh()->activated_at);
    }

    public function test_activation_failure_is_recovered_by_webhook_replay(): void
    {
        [,,$sub] = $this->pending();
        $payment = $this->payment($sub);
        $payload = $this->payload($payment);
        $this->mock(ActivateSubscriptionAction::class)->shouldReceive('execute')->once()->andThrow(new \RuntimeException('simulated'));
        try {
            app(HandleAppyPayWebhook::class)->execute($payload, 'audit');
            self::fail('Expected activation failure.');
        } catch (\RuntimeException $error) {
            self::assertSame('simulated', $error->getMessage());
        }
        self::assertSame('processing', $payment->fresh()->status);
        self::assertSame('pendente', $sub->fresh()->status);
        self::assertSame('failed', WebhookEvent::sole()->status);
        self::assertNull(WebhookEvent::sole()->processed_at);
        $this->app->instance(ActivateSubscriptionAction::class, new ActivateSubscriptionAction);
        self::assertSame('ok', app(HandleAppyPayWebhook::class)->execute($payload, 'audit')->result);
        self::assertSame('ativa', $sub->fresh()->status);
    }

    public function test_unknown_payment_event_can_be_reprocessed_after_payment_exists(): void
    {
        [,,$sub] = $this->pending();
        $payload = ['eventId' => 'early', 'merchantTransactionId' => 'GPO261010120000', 'responseStatus' => ['status' => 'Success']];
        self::assertSame('payment_not_found', app(HandleAppyPayWebhook::class)->execute($payload, 'early')->result);
        self::assertNull(WebhookEvent::sole()->processed_at);
        $payment = $this->payment($sub);
        self::assertSame(1, app(SubscriptionMaintenance::class)->reconcile()['events_processed']);
        self::assertSame('paid', $payment->fresh()->status);
        self::assertSame('ativa', $sub->fresh()->status);
    }

    public function test_transaction_ids_are_distinct_for_the_same_second(): void
    {
        $ids = [];
        for ($i = 0; $i < 1000; $i++) {
            $ids[] = (string) MerchantTransactionId::generate(PaymentMethod::GPO);
        }
        self::assertCount(1000, array_unique($ids));
        self::assertMatchesRegularExpression('/^GPO[A-F0-9]{12}$/', $ids[0]);
    }

    public function test_inactive_plan_is_rejected(): void
    {
        [, $plan, $sub] = $this->pending();
        $sub->forceDelete();
        $plan->update(['status' => 'inactivo']);
        $this->post(route('billing.start'), ['plano_id' => $plan->id, 'modalidade_pagamento' => 'monthly'])->assertSessionHasErrors('plano_id');
        self::assertSame(0, Subscricao::count());
    }

    public function test_paid_checkout_redirect_does_not_require_company_account(): void
    {
        [$empresa,$plan,$sub] = $this->pending();
        $sub->forceDelete();
        self::assertNull($empresa->conta);
        $this->post(route('billing.start'), ['plano_id' => $plan->id, 'modalidade_pagamento' => 'monthly'])->assertRedirect(route('billing.checkout'));
        self::assertSame('pendente', Subscricao::sole()->status);
        $this->get(route('billing.checkout'))->assertOk();
        $this->get(route('billing.plans'))->assertOk();
    }

    public function test_future_subscription_is_not_shown_as_effective(): void
    {
        [$empresa,,$sub] = $this->pending();
        $sub->update(['status' => 'ativa', 'data_inicio' => now()->addMonth(), 'data_expiracao' => now()->addMonths(2)]);
        $data = app(BillingPlanController::class)->index()->getData();
        self::assertNull($data['activeSubscription']);
        self::assertSame($sub->id, $data['futureSubscription']->id);
        self::assertFalse(app(EffectiveModuleResolver::class)->hasSubscription($empresa->id));
        self::assertFalse($empresa->temSubscricaoAtiva());
    }

    public function test_quote_survives_plan_price_change(): void
    {
        [$empresa,$plan,$sub,$user] = $this->pending();
        $sub->forceDelete();
        $sub = app(StartSubscription::class)->execute($empresa->id, $plan->id, BillingCycle::Monthly, $user->id);
        $plan->update(['preco_mensal' => 9000, 'nome' => 'Renamed']);
        [$payment] = app(ReservePayment::class)->execute($sub, PaymentMethod::REF);
        self::assertEquals(1140, $payment->amount);
        self::assertSame('Pago', $sub->commercial_snapshot['plan_name']);
    }

    public function test_gateway_http_success_with_failed_status_triggers_ref_fallback(): void
    {
        [$empresa,,$sub,$user] = $this->pending();
        $this->mock(GpoPaymentGateway::class)->shouldReceive('create')->once()->andReturn(new PaymentGatewayResult(true, PaymentStatus::Failed, 'gpo-id', null, [], 'Declined'));
        $this->mock(RefPaymentGateway::class)->shouldReceive('create')->once()->andReturn(new PaymentGatewayResult(true, PaymentStatus::Pending, 'ref-id', new PaymentReference('12345', '123456789', now()->addDays(3)), []));
        $view = app(StartCheckout::class)->execute(new StartCheckoutData($user->id, $empresa->id, PaymentMethod::GPO, '923456789'));
        self::assertSame(PaymentMethod::REF, $view->method);
        self::assertTrue($view->fallbackApplied);
        self::assertSame('failed', PagamentoOnline::where('method', 'GPO')->sole()->status);
        self::assertSame('pending', PagamentoOnline::where('method', 'REF')->sole()->status);
    }

    public function test_timeout_does_not_start_another_payment(): void
    {
        [$empresa,,$sub,$user] = $this->pending();
        $this->mock(GpoPaymentGateway::class)->shouldReceive('create')->once()->andReturn(PaymentGatewayResult::failure('EXCEPTION', 'Timeout'));
        $this->mock(RefPaymentGateway::class)->shouldNotReceive('create');
        $service = app(StartCheckout::class);
        $service->execute(new StartCheckoutData($user->id, $empresa->id, PaymentMethod::GPO, '923456789'));
        $view = $service->execute(new StartCheckoutData($user->id, $empresa->id, PaymentMethod::REF));
        self::assertSame(PaymentMethod::GPO, $view->method);
        self::assertSame('processing', PagamentoOnline::sole()->status);
    }

    public function test_reservation_is_committed_before_provider_call(): void
    {
        [$empresa,,$sub,$user] = $this->pending();
        $level = DB::transactionLevel(); // Test fixture transaction is intentional.
        $this->mock(GpoPaymentGateway::class)->shouldReceive('create')->once()->andReturnUsing(function () use ($level) {
            self::assertSame($level, DB::transactionLevel());
            self::assertSame(1, PagamentoOnline::count());

            return new PaymentGatewayResult(true, PaymentStatus::Pending, 'id', null, []);
        });
        app(StartCheckout::class)->execute(new StartCheckoutData($user->id, $empresa->id, PaymentMethod::GPO, '923456789'));
    }

    public function test_http_response_cannot_undo_an_early_paid_webhook(): void
    {
        [$empresa,,$sub,$user] = $this->pending();
        $this->mock(GpoPaymentGateway::class)->shouldReceive('create')->once()->andReturnUsing(function () {
            $payment = PagamentoOnline::sole();
            app(HandleAppyPayWebhook::class)->execute($this->payload($payment), 'early-paid');

            return new PaymentGatewayResult(true, PaymentStatus::Pending, null, null, []);
        });
        $view = app(StartCheckout::class)->execute(new StartCheckoutData($user->id, $empresa->id, PaymentMethod::GPO, '923456789'));
        self::assertSame(PaymentStatus::Paid, $view->status);
        self::assertSame('ativa', $sub->fresh()->status);
    }

    public function test_webhook_cannot_change_amount_or_currency(): void
    {
        [,,$sub] = $this->pending();
        $payment = $this->payment($sub);
        $payload = $this->payload($payment);
        $payload['amount'] = 1;
        self::assertSame('invalid_payload', app(HandleAppyPayWebhook::class)->execute($payload, 'bad')->result);
        self::assertSame('processing', $payment->fresh()->status);
        self::assertSame('pendente', $sub->fresh()->status);
    }

    public function test_paid_record_without_activation_is_reconciled(): void
    {
        [,,$sub] = $this->pending();
        $payment = $this->payment($sub);
        $payment->update(['status' => 'paid', 'paid_at' => now()]);
        self::assertSame(1, app(SubscriptionMaintenance::class)->reconcile()['activations_recovered']);
        self::assertSame('ativa', $sub->fresh()->status);
        self::assertSame(0, app(SubscriptionMaintenance::class)->reconcile()['activations_recovered']);
    }

    public function test_operator_cannot_contract_and_finance_can(): void
    {
        [$empresa,$plan,$sub,$user] = $this->pending();
        $sub->forceDelete();
        CompanyRbac::within($empresa->id, fn () => $user->syncRoles([]));
        $this->post(route('billing.start'), ['plano_id' => $plan->id, 'modalidade_pagamento' => 'monthly'])->assertForbidden();
        CompanyRbac::within($empresa->id, function () use ($user, $empresa) {
            $user->assignRole(Role::create(['name' => 'Gestor Financeiro', 'guard_name' => 'web', 'empresa_id' => $empresa->id]));
        });
        $this->post(route('billing.start'), ['plano_id' => $plan->id, 'modalidade_pagamento' => 'monthly'])->assertRedirect(route('billing.checkout'));
    }

    public function test_renewal_preserves_current_period_and_provisions_future_period(): void
    {
        [$empresa,$plan,$sub,$user] = $this->pending();
        $module = Module::create(['module_name' => 'Fixture', 'price' => 1]);
        DB::table('plano_modulos')->insert(['plano_id' => $plan->id, 'modulo_id' => $module->id]);
        $sub->update(['status' => 'ativa', 'activated_at' => now(), 'data_inicio' => now(), 'data_expiracao' => now()->addMonth()]);
        $renewal = app(StartSubscription::class)->execute($empresa->id, $plan->id, BillingCycle::Monthly, $user->id, true);
        $payment = $this->payment($renewal);
        app(HandleAppyPayWebhook::class)->execute($this->payload($payment), 'renewal');
        self::assertEquals($sub->data_expiracao, $renewal->fresh()->data_inicio);
        self::assertSame('ativa', $sub->fresh()->status);
        self::assertFalse($renewal->fresh()->isAtiva());
        self::assertDatabaseHas('activated_modules', ['subscricao_id' => $renewal->id, 'module_id' => $module->id]);
        $this->get(route('billing.plans'))->assertOk()->assertSee('Próximo período confirmado');
    }

    public function test_pending_cancellation_preserves_active_contract(): void
    {
        [$empresa,$plan,$sub,$user] = $this->pending();
        $active = Subscricao::create(['empresa_id' => $empresa->id, 'plano_id' => $plan->id, 'modalidade_pagamento' => 'monthly', 'status' => 'ativa', 'data_inicio' => now(), 'data_expiracao' => now()->addMonth()]);
        $this->post(route('billing.cancel-pending'))->assertRedirect(route('billing.plans'));
        self::assertSame('cancelada', $sub->fresh()->status);
        self::assertTrue($active->fresh()->isAtiva());
    }

    public function test_cancellation_is_refused_while_payment_is_open(): void
    {
        [,,$sub] = $this->pending();
        $this->payment($sub);
        $this->post(route('billing.cancel-pending'))->assertSessionHasErrors('payment');
        self::assertSame('pendente', $sub->fresh()->status);
    }

    public function test_ref_checkout_polls_and_cannot_read_foreign_payment(): void
    {
        [$empresa,,$sub,$user] = $this->pending();
        $own = $this->payment($sub);
        $own->update(['method' => 'REF', 'status' => 'pending']);
        [$otherUser,$otherEmpresa] = $this->createTenant('OTHER');
        $foreign = PagamentoOnline::create(['empresa_id' => $otherEmpresa->id, 'method' => 'REF', 'amount' => 999, 'currency' => 'AOA', 'status' => 'pending', 'merchant_transaction_id' => 'REF123', 'idempotency_key' => 'REF123']);
        $component = Livewire::test(CheckoutPayment::class);
        self::assertTrue($component->instance()->getShouldPollProperty());
        // Server query protects ownership even if an internal caller supplies a foreign ID.
        $instance = $component->instance();
        $instance->paymentId = $foreign->id;
        $instance->refreshPaymentStatus();
        self::assertSame($own->id, $instance->paymentView['payment_id']);
        self::assertSame($empresa->id, $instance->empresaId);
        $this->get(route('billing.receipt', $foreign->id))->assertNotFound();
    }

    public function test_paid_receipt_is_scoped_and_renders(): void
    {
        [,,$sub] = $this->pending();
        $payment = $this->payment($sub);
        app(HandleAppyPayWebhook::class)->execute($this->payload($payment), 'receipt');
        $this->get(route('billing.receipt', $payment->id))->assertOk()->assertSee('Total pago');
    }

    public function test_legacy_entry_redirects_to_canonical_flow(): void
    {
        [$empresa] = $this->pending();
        $this->get(route('subscribe.view', $empresa->id))->assertRedirect(route('billing.plans'));
        Livewire::test(SubscriptionWizard::class)->assertRedirect(route('billing.plans'));
    }

    public function test_plan_review_does_not_block_unmapped_active_plans(): void
    {
        [$empresa,$plan,$sub,$user] = $this->pending();
        $sub->forceDelete();
        $review = app(BillingPlanReview::class)->report();
        self::assertFalse($review['sales_blocked']);
        self::assertSame('commercialized', $review['plans'][0]['classification']);
        self::assertContains('missing_module_mapping', $review['plans'][0]['issues']);
        app(StartSubscription::class)->execute($empresa->id, $plan->id, BillingCycle::Monthly, $user->id);
        self::assertSame(1, Subscricao::count());
    }

    public function test_quotas_apply_to_new_contracts_without_revoking_legacy_access(): void
    {
        [$empresa,$plan,$sub] = $this->pending();
        $plan->update(['limite_utilizadores' => 1, 'limite_processos' => 0, 'limite_armazenamento_gb' => 0]);
        $quote = app(SubscriptionQuote::class)->fromPlan($plan, BillingCycle::Monthly);
        $sub->update(['status' => 'ativa', 'data_inicio' => now(), 'data_expiracao' => now()->addMonth()]);
        app(BillingQuota::class)->assertAvailable($empresa->id, 'users'); // Grandfathered.
        $sub->update(['commercial_snapshot' => $quote]);
        foreach (['users' => 1, 'storage_gb' => 1] as $resource => $additional) {
            try {
                app(BillingQuota::class)->assertAvailable($empresa->id, $resource, $additional);
                self::fail('Quota exceeded.');
            } catch (ValidationException $error) {
                self::assertArrayHasKey('plan_limit', $error->errors());
            }
        }
        self::assertTrue($sub->fresh()->isAtiva());
        app(BillingQuota::class)->assertAvailable($empresa->id, 'processes'); // Await commercial review.
    }

    public function test_expiry_does_not_convert_activations_to_explicit_revocations(): void
    {
        [$empresa,$plan,$sub] = $this->pending();
        $sub->update(['status' => 'ativa', 'data_inicio' => now()->subMonth(), 'data_expiracao' => now(), 'activated_at' => now()->subMonth()]);
        $module = Module::create(['module_name' => 'Fixture', 'price' => 1]);
        DB::table('activated_modules')->insert(['empresa_id' => $empresa->id, 'subscricao_id' => $sub->id, 'module_id' => $module->id, 'activation_date' => now()->subMonth(), 'data_expiracao' => now(), 'active' => true]);
        self::assertSame(1, app(SubscriptionMaintenance::class)->expire());
        self::assertSame('expirada', $sub->fresh()->status);
        self::assertEquals(1, DB::table('activated_modules')->value('active'));
    }

    public function test_zero_paid_cycle_cannot_be_interpreted_as_free(): void
    {
        [$empresa,$plan,$sub,$user] = $this->pending();
        $sub->forceDelete();
        $plan->update(['preco_trimestral' => 0]);
        $this->post(route('billing.start'), ['plano_id' => $plan->id, 'modalidade_pagamento' => 'trimestral'])->assertSessionHasErrors('plano_id');
        self::assertSame(0, Subscricao::count());
    }

    public function test_renewal_notice_is_in_app_and_idempotent(): void
    {
        [$empresa,,$sub,$user] = $this->pending();
        $sub->update(['status' => 'ativa', 'data_inicio' => now()->subMonth(), 'data_expiracao' => now()->addDays(3)]);
        self::assertSame(1, app(SubscriptionMaintenance::class)->renewalNotices());
        self::assertSame(0, app(SubscriptionMaintenance::class)->renewalNotices());
        self::assertSame(1, $user->notifications()->count());
        $this->get(route('billing.plans'))->assertOk()->assertSee('O plano expira');
    }

    public function test_ref_paid_creation_activates_without_waiting_for_webhook(): void
    {
        [$empresa,,$sub,$user] = $this->pending();
        $this->mock(RefPaymentGateway::class)->shouldReceive('create')->once()->andReturn(new PaymentGatewayResult(true, PaymentStatus::Paid, 'paid-ref', null, []));
        $view = app(StartCheckout::class)->execute(new StartCheckoutData($user->id, $empresa->id, PaymentMethod::REF));
        self::assertSame(PaymentStatus::Paid, $view->status);
        self::assertSame('ativa', $sub->fresh()->status);
    }

    public function test_free_contract_activates_without_payment(): void
    {
        [$empresa,$plan,$sub,$user] = $this->pending();
        $sub->forceDelete();
        $plan->update(['is_free' => true, 'preco_mensal' => 0, 'preco_trimestral' => 0, 'preco_semestral' => 0, 'preco_anual' => 0]);
        $this->post(route('billing.start'), ['plano_id' => $plan->id, 'modalidade_pagamento' => 'monthly'])->assertRedirect(route('billing.plans'));
        self::assertSame('ativa', Subscricao::sole()->status);
        self::assertSame(0, PagamentoOnline::count());
    }

    public function test_pending_subscription_recovers_dashboard_redirect_without_account(): void
    {
        [$empresa] = $this->pending();
        $request = Request::create('/dashboard');
        $response = (new CheckSubscription)->handle($request, fn () => response('dashboard'));
        self::assertSame(route('billing.checkout'), $response->getTargetUrl());
    }

    public function test_quote_rejects_nullable_price_and_month_end_does_not_overflow(): void
    {
        [, $plan, $sub] = $this->pending();
        self::assertSame('2026-02-28', $sub->calcularDataExpiracao(Carbon::parse('2026-01-31'))->format('Y-m-d'));
        $plan->preco_trimestral = null;
        $this->expectException(ValidationException::class);
        app(SubscriptionQuote::class)->fromPlan($plan, BillingCycle::Trimestral);
    }

    public function test_livewire_payment_id_cannot_be_overwritten_by_client(): void
    {
        [,,$sub] = $this->pending();
        $this->payment($sub);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test(CheckoutPayment::class)->set('paymentId', 9999);
    }

    public function test_historical_consumed_unknown_event_can_be_recovered(): void
    {
        [,,$sub] = $this->pending();
        $payment = $this->payment($sub);
        WebhookEvent::create(['provider' => 'appypay', 'event_id' => 'legacy', 'processed_at' => now(), 'status' => 'processed']);
        self::assertSame('ok', app(HandleAppyPayWebhook::class)->execute($this->payload($payment, 'legacy'), 'legacy')->result);
        self::assertSame('ativa', $sub->fresh()->status);
    }

    public function test_portal_storage_quota_rejects_upload_before_writing_file(): void
    {
        [$empresa, $plan, $sub, $user] = $this->pending();
        $plan->update(['limite_armazenamento_gb' => 0]);
        $sub->update(['status' => 'ativa', 'data_inicio' => now(), 'data_expiracao' => now()->addMonth(), 'commercial_snapshot' => app(SubscriptionQuote::class)->fromPlan($plan, BillingCycle::Monthly)]);
        $customer = $this->createCustomer($empresa, $user, 'PORTAL');
        $portal = new ClientePortal(['empresa_id' => $empresa->id, 'customer_id' => $customer->id, 'is_active' => true]);
        Storage::fake('s3');
        Gate::before(fn () => true);
        $file = UploadedFile::fake()->create('documento.pdf', 1, 'application/pdf');
        try {
            app(UploadDocumentoClientePortalAction::class)->execute($portal, $file, DocumentoCategoriaEnum::DOCUMENTOS);
            self::fail('Quota exceeded.');
        } catch (ValidationException $error) {
            self::assertArrayHasKey('plan_limit', $error->errors());
        }
        self::assertSame([], Storage::disk('s3')->allFiles());
        self::assertSame(0, DocumentoArquivo::count());
    }
}
