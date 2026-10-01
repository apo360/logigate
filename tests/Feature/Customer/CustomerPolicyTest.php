<?php

namespace Tests\Feature\Customer;

use App\Application\Customer\Actions\DeleteCustomerAction;
use App\Application\Customer\Actions\ToggleCustomerStatusAction;
use App\Models\Customer;
use App\Models\Empresa;
use App\Models\User;
use App\Support\CompanyRbac;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class CustomerPolicyTest extends TestCase
{
    use ProcessoTestFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertTrue(app()->environment('testing'));
        self::assertSame('logigate_testing', DB::connection()->getDatabaseName());
        DB::beginTransaction();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        try {
            TenantContext::clear();
            DB::rollBack();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } finally {
            parent::tearDown();
        }
    }

    private function authenticateInEmpresa(User $user, Empresa $empresa, array $permissions = []): void
    {
        $this->actingAs($user)->withSession(['empresa_id' => (int) $empresa->id]);
        CompanyRbac::activate();
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        CompanyRbac::within((int) $empresa->id, fn () => $user->syncPermissions($permissions));
        CompanyRbac::forgetUser($user);
    }

    private function assertDenied(callable $operation): void
    {
        try {
            $operation();
            self::fail('Expected authorization denial.');
        } catch (AuthorizationException $exception) {
            self::assertSame(403, $exception->status() ?? 403);
        }
    }

    public function test_policy_allows_only_same_tenant_customer_access(): void
    {
        [$userA, $empresaA] = $this->createTenant('CUS-A');
        [$userB, $empresaB] = $this->createTenant('CUS-B');
        $customer = $this->createCustomer($empresaA, $userA, 'CUS-A');

        $this->authenticateInEmpresa($userA, $empresaA, ['customers.view', 'customers.create']);
        self::assertTrue(Gate::allows('view', $customer));
        self::assertTrue(Gate::allows('create', Customer::class));
        self::assertFalse(Gate::forUser($userB)->allows('view', $customer));

        // A foreign actor with the correct capability still cannot access A's customer.
        $this->authenticateInEmpresa($userB, $empresaB, ['customers.view', 'customers.create']);
        self::assertFalse(Gate::allows('view', $customer));
        self::assertTrue(Gate::allows('create', Customer::class));
        self::assertFalse(Gate::forUser(User::factory()->create())->allows('create', Customer::class));
    }

    public function test_membership_without_grants_does_not_authorize_customer_operations(): void
    {
        [$user, $empresa] = $this->createTenant('CUS-NOGRANT');
        $customer = $this->createCustomer($empresa, $user, 'CUS-NOGRANT');
        $customer->update(['is_active' => true]);
        $this->authenticateInEmpresa($user, $empresa);

        foreach (['view', 'delete', 'activate', 'deactivate'] as $ability) {
            self::assertFalse(Gate::allows($ability, $customer));
        }
        self::assertFalse(Gate::allows('create', Customer::class));
        $this->assertDenied(fn () => app(DeleteCustomerAction::class)->execute($customer->id));
        $this->assertDenied(fn () => app(ToggleCustomerStatusAction::class)->execute($customer, false));
        self::assertNull($customer->refresh()->deleted_at);
        self::assertTrue($customer->is_active);
    }

    public function test_authorized_delete_soft_deletes_customer(): void
    {
        [$user, $empresa] = $this->createTenant('CUS-DELETE');
        $customer = $this->createCustomer($empresa, $user, 'CUS-DELETE');
        $this->authenticateInEmpresa($user, $empresa, ['customers.delete']);

        self::assertTrue(app(DeleteCustomerAction::class)->execute($customer->id));
        self::assertNotNull($customer->refresh()->deleted_at);
    }

    public function test_authorized_status_changes_persist_in_both_directions(): void
    {
        [$user, $empresa] = $this->createTenant('CUS-STATUS');
        $customer = $this->createCustomer($empresa, $user, 'CUS-STATUS');
        $this->authenticateInEmpresa($user, $empresa, ['customers.activate', 'customers.deactivate']);
        $action = app(ToggleCustomerStatusAction::class);

        self::assertFalse($action->execute($customer, false)->is_active);
        self::assertTrue($action->execute($customer, true)->is_active);
    }

    public function test_each_status_direction_requires_its_own_grant(): void
    {
        [$user, $empresa] = $this->createTenant('CUS-DIRECTION');
        $customer = $this->createCustomer($empresa, $user, 'CUS-DIRECTION');
        $customer->update(['is_active' => false]);
        $this->authenticateInEmpresa($user, $empresa, ['customers.deactivate']);
        $action = app(ToggleCustomerStatusAction::class);
        $this->assertDenied(fn () => $action->execute($customer, true));
        self::assertFalse($customer->refresh()->is_active);

        $customer->update(['is_active' => true]);
        $this->authenticateInEmpresa($user, $empresa, ['customers.activate']);
        $this->assertDenied(fn () => $action->execute($customer, false));
        self::assertTrue($customer->refresh()->is_active);
    }

    public function test_foreign_company_grants_cannot_delete_or_change_status(): void
    {
        [$owner, $empresaA] = $this->createTenant('CUS-OWNER');
        [$actor, $empresaB] = $this->createTenant('CUS-FOREIGN');
        $customer = $this->createCustomer($empresaA, $owner, 'CUS-OWNER');
        $customer->update(['is_active' => true]);
        $this->authenticateInEmpresa($actor, $empresaB, [
            'customers.delete', 'customers.activate', 'customers.deactivate',
        ]);

        $this->assertDenied(fn () => app(DeleteCustomerAction::class)->execute($customer->id));
        $this->assertDenied(fn () => app(ToggleCustomerStatusAction::class)->execute($customer, false));
        self::assertNull($customer->refresh()->deleted_at);
        self::assertTrue($customer->is_active);
    }

    public function test_missing_active_company_denies_even_with_grants(): void
    {
        [$user, $empresa] = $this->createTenant('CUS-NOCONTEXT');
        $customer = $this->createCustomer($empresa, $user, 'CUS-NOCONTEXT');
        $this->authenticateInEmpresa($user, $empresa, [
            'customers.view', 'customers.create', 'customers.delete', 'customers.deactivate',
        ]);
        TenantContext::clear();

        self::assertFalse(Gate::allows('view', $customer));
        self::assertFalse(Gate::allows('create', Customer::class));
        $this->assertDenied(fn () => app(DeleteCustomerAction::class)->execute($customer->id));
        $this->assertDenied(fn () => app(ToggleCustomerStatusAction::class)->execute($customer, false));
    }

    public function test_shared_profile_remains_protected_from_global_mutations(): void
    {
        [$user, $empresaA] = $this->createTenant('CUS-SHARED-A');
        [, $empresaB] = $this->createTenant('CUS-SHARED-B');
        $customer = $this->createCustomer($empresaA, $user, 'CUS-SHARED');
        $customer->empresas()->attach($empresaB->id);
        $this->authenticateInEmpresa($user, $empresaA, [
            'customers.view', 'customers.delete', 'customers.deactivate',
        ]);

        self::assertTrue(Gate::allows('view', $customer));
        $this->assertDenied(fn () => app(DeleteCustomerAction::class)->execute($customer->id));
        $this->assertDenied(fn () => app(ToggleCustomerStatusAction::class)->execute($customer, false));
        self::assertNull($customer->refresh()->deleted_at);
    }
}
