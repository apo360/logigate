<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use App\Models\Empresa;
use App\Application\Customer\Actions\DeleteCustomerAction;
use App\Application\Customer\Actions\ToggleCustomerStatusAction;
use App\Domains\Customers\Services\CustomerService;
use App\Domains\Customers\Data\CustomerFormData;
use App\Support\CompanyRbac;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class CustomerPolicyTest extends TestCase
{
    use ProcessoTestFixtures;

    private bool $transactionStarted = false;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertTrue(app()->environment('testing'));
        self::assertSame('mysql', config('database.default'));
        self::assertContains(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost']);
        self::assertSame('logigate_testing', config('database.connections.mysql.database'));
        self::assertSame('logigate_testing', DB::connection()->getDatabaseName());
        DB::beginTransaction();
        $this->transactionStarted = true;
    }

    protected function tearDown(): void
    {
        if ($this->transactionStarted) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function selectActor(User $user, Empresa $empresa, array $permissions = []): void
    {
        $this->actingAs($user);
        TenantContext::setEmpresa($user, $empresa);
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->syncPermissions($permissions);
        CompanyRbac::forgetUser($user);
    }

    private function assertDenied(callable $operation): void
    {
        try {
            $operation();
            self::fail('Expected authorization denial.');
        } catch (AuthorizationException $exception) {
            self::assertInstanceOf(AuthorizationException::class, $exception);
        }
    }

    public function test_policy_allows_only_same_tenant_customer_access(): void
    {
        [$tenantAUser, $tenantAEmpresa] = $this->createTenant('CUS-A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('CUS-B');

        $customer = $this->createCustomer($tenantAEmpresa, $tenantAUser, 'CUS-A');

        $this->selectActor($tenantAUser, $tenantAEmpresa, ['customers.view', 'customers.create']);
        $this->assertTrue(Gate::allows('view', $customer));
        $this->assertTrue(Gate::allows('create', $customer::class));
        $this->selectActor($tenantBUser, $tenantBEmpresa, ['customers.view', 'customers.create']);
        $this->assertFalse(Gate::allows('view', $customer));
        $this->assertFalse(Gate::forUser(User::factory()->create())->allows('create', $customer::class));
    }

    public function test_authorized_actions_resolve_and_change_status_then_delete(): void
    {
        [$user, $empresa] = $this->createTenant('CUS-ACTIONS');
        $customer = $this->createCustomer($empresa, $user, 'CUS-ACTIONS');
        $this->selectActor($user, $empresa, ['customers.activate', 'customers.deactivate', 'customers.delete']);

        $toggle = app(ToggleCustomerStatusAction::class);
        self::assertFalse((bool) $toggle->execute($customer, false)->is_active);
        self::assertTrue((bool) $toggle->execute($customer, true)->is_active);
        self::assertTrue(app(DeleteCustomerAction::class)->execute((int) $customer->id));
        self::assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_actions_reject_missing_permissions_and_foreign_company(): void
    {
        [$user, $empresa] = $this->createTenant('CUS-DENY');
        [$foreignUser, $foreignEmpresa] = $this->createTenant('CUS-FOREIGN');
        $customer = $this->createCustomer($empresa, $user, 'CUS-DENY');
        $customer->forceFill(['is_active' => true])->save();
        $toggle = app(ToggleCustomerStatusAction::class);
        $delete = app(DeleteCustomerAction::class);

        $this->selectActor($user, $empresa);
        $this->assertDenied(fn () => $toggle->execute($customer, false));
        $this->assertDenied(fn () => $toggle->execute($customer, true));
        $this->assertDenied(fn () => $delete->execute((int) $customer->id));
        $this->selectActor($foreignUser, $foreignEmpresa, ['customers.activate', 'customers.deactivate', 'customers.delete']);
        $this->assertDenied(fn () => $toggle->execute($customer, false));
        $this->assertDenied(fn () => $toggle->execute($customer, true));
        $this->assertDenied(fn () => $delete->execute((int) $customer->id));
        self::assertTrue((bool) $customer->refresh()->is_active);
    }

    public function test_shared_profile_remains_protected(): void
    {
        [$user, $empresa] = $this->createTenant('CUS-SHARED');
        [, $otherEmpresa] = $this->createTenant('CUS-SHARED-B');
        $customer = $this->createCustomer($empresa, $user, 'CUS-SHARED');
        $customer->empresas()->attach($otherEmpresa->id);
        $this->selectActor($user, $empresa, ['customers.delete', 'customers.deactivate']);
        $this->assertDenied(fn () => app(ToggleCustomerStatusAction::class)->execute($customer, false));
        $this->assertDenied(fn () => app(DeleteCustomerAction::class)->execute((int) $customer->id));
    }

    public function test_permissions_from_another_company_and_missing_active_company_are_rejected(): void
    {
        [$user, $empresa] = $this->createTenant('CUS-CONTEXT');
        [, $otherEmpresa] = $this->createTenant('CUS-CONTEXT-B');
        $user->empresas()->attach($otherEmpresa->id);
        $customer = $this->createCustomer($empresa, $user, 'CUS-CONTEXT');
        $this->selectActor($user, $otherEmpresa, ['customers.delete', 'customers.deactivate']);
        TenantContext::setEmpresa($user, $empresa);
        $this->assertDenied(fn () => app(ToggleCustomerStatusAction::class)->execute($customer, false));
        $this->assertDenied(fn () => app(DeleteCustomerAction::class)->execute((int) $customer->id));
        $this->selectActor($user, $empresa, ['customers.delete', 'customers.deactivate']);
        TenantContext::clear();
        $this->assertDenied(fn () => app(ToggleCustomerStatusAction::class)->execute($customer, false));
        $this->assertDenied(fn () => app(DeleteCustomerAction::class)->execute((int) $customer->id));
    }

    public function test_service_resolves_and_adapts_form_to_creation_contract(): void
    {
        [$user, $empresa] = $this->createTenant('CUS-SERVICE');
        $this->selectActor($user, $empresa, ['customers.create']);
        $data = CustomerFormData::fromArray([
            'CustomerTaxID' => '9901234567',
            'CompanyName' => 'Cliente Service',
            'CustomerType' => 'Empresa',
            'Email' => 'service@example.test',
            'AccountID' => 'ACC-SERVICE',
        ]);
        $customer = app(CustomerService::class)->create($data, $empresa);
        self::assertSame('Cliente Service', $customer->CompanyName);
        self::assertSame('9901234567', $customer->CustomerTaxID);
        self::assertSame((int) $empresa->id, (int) $customer->empresa_id);
        self::assertSame((int) $user->id, (int) $customer->user_id);
        self::assertTrue($customer->empresas()->whereKey($empresa->id)->exists());
    }
}
