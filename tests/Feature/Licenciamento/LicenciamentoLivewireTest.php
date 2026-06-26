<?php

namespace Tests\Feature\Licenciamento;

use App\Livewire\Licenciamento\LiicenciamentoCreate;
use App\Livewire\Licenciamento\LiicenciamentoEdit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class LicenciamentoLivewireTest extends TestCase
{
    use DatabaseTransactions;
    use LicenciamentoTestSupport;
    use ProcessoTestFixtures;

    public function test_create_rejects_cross_tenant_customer_and_exportador_ids(): void
    {
        [$tenantAUser] = $this->createTenant('LW-LIC-A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('LW-LIC-B');
        $this->grantLicenciamentoPermissions($tenantAUser);
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

    public function test_create_stores_licenciamento_for_current_tenant(): void
    {
        [$user, $empresa] = $this->createTenant('LW-LIC-CREATE');
        $this->grantLicenciamentoPermissions($user);
        [$estanciaId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'LW-LIC-CREATE');
        $exportador = $this->createExportador($empresa, $user, 'LW-LIC-CREATE');

        $this->actingAs($user);

        $component = Livewire::test(LiicenciamentoCreate::class);

        foreach ($this->validLicenciamentoPayload($estanciaId, $customer->id, $exportador->id, [
            'descricao' => 'Licenciamento criado via Livewire',
        ]) as $field => $value) {
            $component->set($field, $value);
        }

        $component->call('create')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('licenciamentos', [
            'empresa_id' => $empresa->id,
            'cliente_id' => $customer->id,
            'exportador_id' => $exportador->id,
            'descricao' => 'Licenciamento criado via Livewire',
        ]);
    }

    public function test_edit_updates_licenciamento_for_current_tenant(): void
    {
        [$user, $empresa] = $this->createTenant('LW-LIC-EDIT');
        $this->grantLicenciamentoPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'LW-LIC-EDIT');

        $this->actingAs($user);

        Livewire::test(LiicenciamentoEdit::class, ['licenciamento' => $licenciamento])
            ->set('descricao', 'Licenciamento atualizado via Livewire')
            ->call('update')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('licenciamentos', [
            'id' => $licenciamento->id,
            'empresa_id' => $empresa->id,
            'descricao' => 'Licenciamento atualizado via Livewire',
        ]);
    }
}
