<?php

namespace Tests\Feature\Licenciamento;

use App\Livewire\Licenciamento\LiicenciamentoCreate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class LicenciamentoLivewireTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_create_rejects_cross_tenant_customer_and_exportador_ids(): void
    {
        [$tenantAUser] = $this->createTenant('LW-LIC-A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('LW-LIC-B');
        [$estanciaId] = $this->createLookupData();

        $customerB = $this->createCustomer($tenantBEmpresa, $tenantBUser, 'LW-LIC-B');
        $exportadorB = $this->createExportador($tenantBEmpresa, $tenantBUser, 'LW-LIC-B');

        $this->actingAs($tenantAUser);

        Livewire::test(LiicenciamentoCreate::class)
            ->set('cliente_id', $customerB->id)
            ->set('exportador_id', $exportadorB->id)
            ->set('estancia_id', $estanciaId)
            ->set('referencia_cliente', 'REF-LIC')
            ->set('factura_proforma', 'FP-LIC')
            ->set('descricao', 'Licenciamento Teste')
            ->set('moeda', 'AOA')
            ->set('tipo_declaracao', '11')
            ->set('tipo_transporte', '3')
            ->set('metodo_avaliacao', 'GATT')
            ->set('codigo_volume', 'B')
            ->set('qntd_volume', 1)
            ->set('forma_pagamento', 'RD')
            ->set('fob_total', 100)
            ->call('create')
            ->assertHasErrors(['cliente_id', 'exportador_id']);
    }
}
