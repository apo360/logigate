<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class CustomerPolicyTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_policy_allows_only_same_tenant_customer_access(): void
    {
        [$tenantAUser, $tenantAEmpresa] = $this->createTenant('CUS-A');
        [$tenantBUser] = $this->createTenant('CUS-B');

        $customer = $this->createCustomer($tenantAEmpresa, $tenantAUser, 'CUS-A');

        $this->assertTrue(Gate::forUser($tenantAUser)->allows('view', $customer));
        $this->assertFalse(Gate::forUser($tenantBUser)->allows('view', $customer));
        $this->assertTrue(Gate::forUser($tenantAUser)->allows('create', $customer::class));
        $this->assertFalse(Gate::forUser(User::factory()->create())->allows('create', $customer::class));
    }
}
