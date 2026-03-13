<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_cannot_access_another_tenant_customer(): void
    {
        [$tenantAUser] = $this->createTenantUser('A');
        [, $tenantBEmpresa] = $this->createTenantUser('B');

        $customerId = DB::table('customers')->insertGetId([
            'CustomerID' => 'cliB-001',
            'AccountID' => 'ACC-B-001',
            'CustomerTaxID' => 'NIF-B-001',
            'CompanyName' => 'Tenant B Customer',
            'Telephone' => null,
            'Email' => 'tenantb-customer@example.com',
            'Website' => null,
            'SelfBillingIndicator' => 0,
            'user_id' => User::query()->where('email', 'tenant-b@example.com')->value('id'),
            'empresa_id' => $tenantBEmpresa->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($tenantAUser)
            ->get("/customers/{$customerId}")
            ->assertNotFound();
    }

    public function test_tenant_cannot_finalize_another_tenant_process(): void
    {
        [$tenantAUser] = $this->createTenantUser('A2');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenantUser('B2');

        $customerId = DB::table('customers')->insertGetId([
            'CustomerID' => 'cliB-002',
            'AccountID' => 'ACC-B-002',
            'CustomerTaxID' => 'NIF-B-002',
            'CompanyName' => 'Tenant B Process Customer',
            'Telephone' => null,
            'Email' => 'tenantb-process@example.com',
            'Website' => null,
            'SelfBillingIndicator' => 0,
            'user_id' => $tenantBUser->id,
            'empresa_id' => $tenantBEmpresa->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $processoId = DB::table('processos')->insertGetId([
            'NrProcesso' => 'PR-B-0002',
            'ContaDespacho' => null,
            'RefCliente' => null,
            'Descricao' => 'Processo Tenant B',
            'DataAbertura' => now()->toDateString(),
            'DataFecho' => null,
            'TipoProcesso' => null,
            'Estado' => 'aberto',
            'customer_id' => $customerId,
            'user_id' => $tenantBUser->id,
            'empresa_id' => $tenantBEmpresa->id,
            'forma_pagamento' => 'TRF',
            'codigo_banco' => '001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($tenantAUser)
            ->postJson("/processo/finalizar/{$processoId}")
            ->assertNotFound();
    }

    public function test_tenant_cannot_download_files_from_another_tenant_namespace(): void
    {
        [$tenantAUser] = $this->createTenantUser('A3');
        [, $tenantBEmpresa] = $this->createTenantUser('B3');

        $this->actingAs($tenantAUser)
            ->get("/arquivo/download/empresa/{$tenantBEmpresa->id}/files/private.pdf")
            ->assertForbidden();
    }

    /**
     * Create one user and one company and bind both in empresa_users.
     */
    private function createTenantUser(string $suffix): array
    {
        $user = User::factory()->create([
            'email' => "tenant-" . strtolower($suffix) . "@example.com",
        ]);

        $empresa = Empresa::query()->create([
            'CodFactura' => 'CF-' . $suffix,
            'CodProcesso' => 'CP-' . $suffix,
            'Empresa' => 'Empresa ' . $suffix,
            'ActividadeComercial' => 'Servicos',
            'Designacao' => 'Outro',
            'NIF' => '900000' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT) . $suffix,
            'Cedula' => 'CED-' . $suffix . '-' . random_int(1000, 9999),
            'Endereco_completo' => 'Rua Teste ' . $suffix,
            'Provincia' => 'Luanda',
            'Cidade' => 'Luanda',
            'Email' => "empresa-{$suffix}@example.com",
            'Contacto_movel' => '900000000',
            'Contacto_fixo' => '222000000',
            'Sigla' => 'EMP' . strtoupper($suffix),
        ]);

        DB::table('empresa_users')->insert([
            'conta' => $empresa->conta,
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $empresa];
    }
}
