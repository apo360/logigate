<?php

namespace Tests\Feature\Processo;

use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProcessoTenantIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_tenant_cannot_access_or_operate_on_another_tenant_process(): void
    {
        [$tenantAUser] = $this->createTenant('A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('B');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();

        $customerB = $this->createCustomer($tenantBEmpresa, $tenantBUser, 'B');
        $exportadorB = $this->createExportador($tenantBEmpresa, $tenantBUser, 'B');
        $processoB = $this->createProcesso($tenantBEmpresa, $tenantBUser, $customerB, $exportadorB, $estanciaId, $tipoProcessoId);

        $this->actingAs($tenantAUser)->get(route('processos.show', $processoB))->assertNotFound();
        $this->actingAs($tenantAUser)->get(route('processos.edit', $processoB))->assertNotFound();
        $this->actingAs($tenantAUser)->postJson(route('processo.finalizar', $processoB->id))->assertNotFound();
        $this->actingAs($tenantAUser)->get(route('processos.print', $processoB->id))->assertNotFound();
        $this->actingAs($tenantAUser)->get(route('gerar.xml', $processoB->id))->assertNotFound();
    }

    public function test_process_number_duplicate_check_is_scoped_per_empresa(): void
    {
        [$tenantAUser, $tenantAEmpresa] = $this->createTenant('A2');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('B2');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();

        $customerA = $this->createCustomer($tenantAEmpresa, $tenantAUser, 'A2');
        $exportadorA = $this->createExportador($tenantAEmpresa, $tenantAUser, 'A2');
        $customerB = $this->createCustomer($tenantBEmpresa, $tenantBUser, 'B2');
        $exportadorB = $this->createExportador($tenantBEmpresa, $tenantBUser, 'B2');

        $this->createProcesso($tenantAEmpresa, $tenantAUser, $customerA, $exportadorA, $estanciaId, $tipoProcessoId, [
            'NrProcesso' => 'PROC-2026-000001',
        ]);

        $processo = app(CriarProcessoAction::class)->execute(CriarProcessoDTO::fromArray([
            'NrProcesso' => 'PROC-2026-000001',
            'customer_id' => $customerB->id,
            'user_id' => $tenantBUser->id,
            'empresa_id' => $tenantBEmpresa->id,
            'exportador_id' => $exportadorB->id,
            'estancia_id' => $estanciaId,
            'TipoProcesso' => (string) $tipoProcessoId,
            'Estado' => 'Aberto',
            'DataAbertura' => now()->toDateString(),
        ]));

        $this->assertSame($tenantBEmpresa->id, (int) $processo->empresa_id);
        $this->assertSame('PROC-2026-000001', $processo->NrProcesso);
    }
}
