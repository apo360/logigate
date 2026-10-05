<?php

namespace Tests\Feature\Processo;

use App\Models\Processo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProcessoPolicyTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_policy_allows_only_same_tenant_process_operations(): void
    {
        [$tenantAUser, $tenantAEmpresa] = $this->createTenant('A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('B');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();

        $customerA = $this->createCustomer($tenantAEmpresa, $tenantAUser, 'A');
        $exportadorA = $this->createExportador($tenantAEmpresa, $tenantAUser, 'A');
        $processoA = $this->createProcesso($tenantAEmpresa, $tenantAUser, $customerA, $exportadorA, $estanciaId, $tipoProcessoId);

        $permissions = ['processos.view', 'processos.create', 'processos.update', 'processos.delete', 'processos.finalize', 'processos.print', 'processos.export_xml', 'processos.simulate'];
        $this->grantTenantPermissions($tenantAUser, $permissions);
        $this->grantTenantPermissions($tenantBUser, $permissions);

        foreach (['view', 'update', 'delete', 'finalize', 'print', 'exportXml', 'simulate'] as $ability) {
            $this->signInTenant($tenantAUser);
            $this->assertTrue(Gate::forUser($tenantAUser)->allows($ability, $processoA));
            $this->signInTenant($tenantBUser);
            $this->assertFalse(Gate::forUser($tenantBUser)->allows($ability, $processoA));
        }

        $this->signInTenant($tenantAUser);
        $this->assertTrue(Gate::forUser($tenantAUser)->allows('create', Processo::class));
        $this->assertFalse(Gate::forUser(User::factory()->create())->allows('create', Processo::class));
    }
}
