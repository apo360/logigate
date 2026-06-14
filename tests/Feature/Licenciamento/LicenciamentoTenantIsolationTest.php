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
    use ProcessoTestFixtures;

    public function test_tenant_cannot_view_another_tenant_licenciamento(): void
    {
        if (!Schema::hasTable('licenciamentos')) {
            $this->markTestSkipped('licenciamentos table is not available in this environment.');
        }

        [$tenantAUser] = $this->createTenant('FT-LIC-A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('FT-LIC-B');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();

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
            'tipo_declaracao' => (string) $tipoProcessoId === 'Importação' ? '11' : (string) $tipoProcessoId,
            'tipo_transporte' => '3',
            'metodo_avaliacao' => 'GATT',
            'codigo_volume' => 'B',
            'qntd_volume' => 1,
            'forma_pagamento' => 'RD',
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

    private function onlyExistingColumns(string $table, array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(Schema::getColumnListing($table)));
    }
}
