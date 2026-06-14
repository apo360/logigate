<?php

namespace Tests\Feature\Processo;

use App\Livewire\Processo\ProcessoCreate;
use App\Livewire\Processo\ProcessoEdit;
use App\Livewire\Processo\ProcessoShow;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ProcessoLivewireTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_create_rejects_cross_tenant_customer_and_exportador_ids(): void
    {
        [$tenantAUser, $tenantAEmpresa] = $this->createTenant('A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('B');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();

        $customerB = $this->createCustomer($tenantBEmpresa, $tenantBUser, 'B');
        $exportadorB = $this->createExportador($tenantBEmpresa, $tenantBUser, 'B');

        $this->actingAs($tenantAUser);

        Livewire::test(ProcessoCreate::class)
            ->set('customer_id', $customerB->id)
            ->set('exportador_id', $exportadorB->id)
            ->set('estancia_id', $estanciaId)
            ->set('vinheta', 'V-001')
            ->set('TipoProcesso', (string) $tipoProcessoId)
            ->call('save')
            ->assertHasErrors(['customer_id', 'exportador_id']);

        $this->assertSame(0, DB::table('processos')->where('empresa_id', $tenantAEmpresa->id)->count());
    }

    public function test_edit_hydrates_aliases_and_recalculates_values(): void
    {
        [$tenantUser, $tenantEmpresa] = $this->createTenant('A2');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($tenantEmpresa, $tenantUser, 'A2');
        $exportador = $this->createExportador($tenantEmpresa, $tenantUser, 'A2');
        $processo = $this->createProcesso($tenantEmpresa, $tenantUser, $customer, $exportador, $estanciaId, $tipoProcessoId, [
            'N_Dar' => 77,
            'MarcaFiscal' => 'MF-77',
            'Cambio' => 2,
        ]);

        $this->actingAs($tenantUser);

        $component = Livewire::test(ProcessoEdit::class, ['processo' => $processo]);

        if (Schema::hasColumn('processos', 'N_Dar')) {
            $component->assertSet('NrDAR', 77);
        }

        if (Schema::hasColumn('processos', 'MarcaFiscal')) {
            $component->assertSet('NrMarcaFiscal', 'MF-77');
        }

        $component
            ->set('fob_total', 200)
            ->set('frete', 25)
            ->set('seguro', 5)
            ->set('Cambio', 2)
            ->call('update')
            ->assertHasNoErrors();

        $processo->refresh();
        if (Schema::hasColumn('processos', 'cif')) {
            $this->assertSame(230.0, (float) $processo->cif);
        }

        if (Schema::hasColumn('processos', 'ValorAduaneiro')) {
            $this->assertSame(460.0, (float) $processo->ValorAduaneiro);
        }
    }

    public function test_show_rejects_cross_tenant_process(): void
    {
        [$tenantAUser] = $this->createTenant('A3');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('B3');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customerB = $this->createCustomer($tenantBEmpresa, $tenantBUser, 'B3');
        $exportadorB = $this->createExportador($tenantBEmpresa, $tenantBUser, 'B3');
        $processoB = $this->createProcesso($tenantBEmpresa, $tenantBUser, $customerB, $exportadorB, $estanciaId, $tipoProcessoId);

        $this->actingAs($tenantAUser);

        Livewire::test(ProcessoShow::class, ['processo' => $processoB])
            ->assertForbidden();
    }
}
