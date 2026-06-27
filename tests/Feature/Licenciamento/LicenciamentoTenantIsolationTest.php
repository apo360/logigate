<?php

namespace Tests\Feature\Licenciamento;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class LicenciamentoTenantIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use LicenciamentoTestSupport;
    use ProcessoTestFixtures;

    public function test_tenant_user_can_open_crud_pages_for_own_licenciamento(): void
    {
        [$user, $empresa] = $this->createTenant('FT-LIC-OWN');
        $this->grantLicenciamentoPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'FT-LIC-OWN');

        $this->actingAs($user)
            ->get(route('licenciamentos.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('licenciamentos.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('licenciamentos.show', $licenciamento))
            ->assertOk()
            ->assertSee('Ações Rápidas')
            ->assertSee('Gerir Mercadorias');

        $this->actingAs($user)
            ->get(route('licenciamentos.edit', $licenciamento))
            ->assertOk();
    }

    public function test_tenant_cannot_view_another_tenant_licenciamento(): void
    {
        if (!Schema::hasTable('licenciamentos')) {
            $this->markTestSkipped('licenciamentos table is not available in this environment.');
        }

        [$tenantAUser] = $this->createTenant('FT-LIC-A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('FT-LIC-B');
        $this->grantLicenciamentoPermissions($tenantAUser);
        [$estanciaId] = $this->createLookupData();

        $customerB = $this->createCustomer($tenantBEmpresa, $tenantBUser, 'FT-LIC-B');
        $exportadorB = $this->createExportador($tenantBEmpresa, $tenantBUser, 'FT-LIC-B');

        $licenciamentoId = DB::table('licenciamentos')->insertGetId($this->onlyExistingColumns('licenciamentos', [
            'codigo_licenciamento' => 'LIC-FT-' . random_int(1000, 9999),
            'estancia_id' => $estanciaId,
            'cliente_id' => $customerB->id,
            'exportador_id' => $exportadorB->id,
            'empresa_id' => $tenantBEmpresa->id,
            'referencia_cliente' => 'REF-FT-LIC',
            'factura_proforma' => 'FP-FT-LIC',
            'descricao' => 'Licenciamento tenant B',
            'moeda' => 'AOA',
            'tipo_declaracao' => '11',
            'tipo_transporte' => '3',
            'registo_transporte' => '',
            'manifesto' => '',
            'data_entrada' => null,
            'porto_entrada' => 'LAD',
            'peso_bruto' => 100,
            'adicoes' => 1,
            'metodo_avaliacao' => 'GATT',
            'codigo_volume' => 'B',
            'qntd_volume' => 1,
            'forma_pagamento' => 'RD',
            'codigo_banco' => '',
            'fob_total' => 100,
            'frete' => 0,
            'seguro' => 0,
            'cif' => 100,
            'status_fatura' => 'pendente',
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $this->actingAs($tenantAUser)
            ->get(route('licenciamentos.show', $licenciamentoId))
            ->assertNotFound();
    }

    public function test_tenant_cannot_edit_another_tenant_licenciamento(): void
    {
        [$tenantAUser] = $this->createTenant('FT-LIC-EDIT-A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('FT-LIC-EDIT-B');
        $this->grantLicenciamentoPermissions($tenantAUser);

        $licenciamento = $this->createLicenciamentoFor($tenantBEmpresa, $tenantBUser, 'FT-LIC-EDIT-B');

        $this->actingAs($tenantAUser)
            ->get(route('licenciamentos.edit', $licenciamento->id))
            ->assertNotFound();
    }

    public function test_tenant_cannot_delete_another_tenant_licenciamento(): void
    {
        [$tenantAUser] = $this->createTenant('FT-LIC-DELETE-A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('FT-LIC-DELETE-B');
        $this->grantLicenciamentoPermissions($tenantAUser);

        $licenciamento = $this->createLicenciamentoFor($tenantBEmpresa, $tenantBUser, 'FT-LIC-DELETE-B');

        $this->actingAs($tenantAUser)
            ->delete(route('licenciamentos.destroy', $licenciamento->id))
            ->assertNotFound();
    }

    private function onlyExistingColumns(string $table, array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(Schema::getColumnListing($table)));
    }
}
