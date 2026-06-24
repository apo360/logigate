<?php

namespace Tests\Feature\Portal;

use App\Models\ClientePortal;
use App\Models\Customer;
use App\Models\DocumentoArquivo;
use App\Models\Empresa;
use App\Models\Licenciamento;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientePortalSecurityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_portal_user_can_login_with_username_email_or_phone(): void
    {
        [$portal] = $this->createPortalUser('login-identifiers');

        foreach (['username' => $portal->username, 'email' => $portal->email, 'phone' => $portal->phone] as $identifier => $login) {
            auth('cliente_portal')->logout();

            $response = $this->post(route('cliente.portal.login.submit'), [
                'login' => $login,
                'password' => 'secret-password',
            ]);

            $response->assertRedirect(route('cliente.portal.dashboard'));
            $this->assertAuthenticatedAs($portal->fresh(), 'cliente_portal');
        }
    }

    public function test_inactive_portal_user_cannot_login(): void
    {
        [$portal] = $this->createPortalUser('inactive', ['is_active' => false]);

        $this->post(route('cliente.portal.login.submit'), [
            'login' => $portal->username,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest('cliente_portal');
    }

    public function test_unauthenticated_portal_dashboard_redirects_to_login(): void
    {
        $this->get(route('cliente.portal.dashboard'))
            ->assertRedirect(route('cliente.portal.login'));
    }

    public function test_portal_user_can_only_resolve_own_processo(): void
    {
        [$portal, $customer] = $this->createPortalUser('own-processo');
        [, $otherCustomer] = $this->createPortalUser('other-processo');

        $ownProcesso = $this->createProcesso($customer, 'OWN');
        $otherProcesso = $this->createProcesso($otherCustomer, 'OTHER');

        $this->actingAs($portal, 'cliente_portal')
            ->get(route('cliente.portal.processos.show', $ownProcesso->id))
            ->assertRedirect(route('cliente.portal.dashboard'));

        $this->actingAs($portal, 'cliente_portal')
            ->get(route('cliente.portal.processos.show', $otherProcesso->id))
            ->assertNotFound();
    }

    public function test_portal_user_can_only_resolve_own_licenciamento(): void
    {
        [$portal, $customer] = $this->createPortalUser('own-licenciamento');
        [, $otherCustomer] = $this->createPortalUser('other-licenciamento');

        $ownLicenciamento = $this->createLicenciamento($customer, 'OWN');
        $otherLicenciamento = $this->createLicenciamento($otherCustomer, 'OTHER');

        $this->actingAs($portal, 'cliente_portal')
            ->get(route('cliente.portal.licenciamentos.show', $ownLicenciamento->id))
            ->assertOk();

        $this->actingAs($portal, 'cliente_portal')
            ->get(route('cliente.portal.licenciamentos.show', $otherLicenciamento->id))
            ->assertNotFound();
    }

    public function test_private_documents_are_denied_to_portal_users(): void
    {
        [$portal, $customer] = $this->createPortalUser('document-private');
        $documento = DocumentoArquivo::query()->create($this->onlyExistingColumns('documentos_arquivos', [
            'uuid' => (string) Str::uuid(),
            'empresa_id' => $customer->empresa_id,
            'customer_id' => $customer->id,
            'contexto' => 'cliente',
            'categoria' => 'teste',
            'visibilidade' => 'privado',
            'storage_disk' => 's3',
            'storage_key' => 'empresa/' . $customer->empresa_id . '/files/private.pdf',
            'nome_original' => 'private.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $this->actingAs($portal, 'cliente_portal')
            ->get(route('cliente.portal.documentos.download', $documento->id))
            ->assertForbidden();
    }

    public function test_empresa_context_allows_only_direct_or_associated_empresas(): void
    {
        [$portal, $customer] = $this->createPortalUser('empresa-context');
        $associatedEmpresa = $this->createEmpresa('ASSOC');
        $unrelatedEmpresa = $this->createEmpresa('UNRELATED');

        $customer->empresas()->syncWithoutDetaching([$associatedEmpresa->id]);

        $this->actingAs($portal, 'cliente_portal')
            ->post(route('cliente.portal.empresa-context.update'), ['empresa_id' => $customer->empresa_id])
            ->assertRedirect();

        $this->assertSame((int) $customer->empresa_id, session('cliente_portal_empresa_id'));

        $this->actingAs($portal, 'cliente_portal')
            ->post(route('cliente.portal.empresa-context.update'), ['empresa_id' => $associatedEmpresa->id])
            ->assertRedirect();

        $this->assertSame((int) $associatedEmpresa->id, session('cliente_portal_empresa_id'));

        $this->actingAs($portal, 'cliente_portal')
            ->post(route('cliente.portal.empresa-context.update'), ['empresa_id' => $unrelatedEmpresa->id])
            ->assertForbidden();
    }

    private function createPortalUser(string $suffix, array $portalOverrides = []): array
    {
        $user = User::factory()->create([
            'email' => "portal-owner-{$suffix}@example.test",
        ]);
        $empresa = $this->createEmpresa($suffix);
        $customer = $this->createCustomer($empresa, $user, $suffix);

        $portal = ClientePortal::query()->create(array_merge([
            'customer_id' => $customer->id,
            'empresa_id' => $empresa->id,
            'username' => "portal-{$suffix}",
            'email' => "portal-{$suffix}@example.test",
            'phone' => '923' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'password' => Hash::make('secret-password'),
            'is_active' => true,
        ], $portalOverrides));

        return [$portal, $customer, $empresa, $user];
    }

    private function createEmpresa(string $suffix): Empresa
    {
        $id = DB::table('empresas')->insertGetId($this->onlyExistingColumns('empresas', [
            'CodFactura' => "CF-{$suffix}",
            'CodProcesso' => "CP-{$suffix}",
            'Empresa' => "Empresa {$suffix}",
            'ActividadeComercial' => 'Servicos',
            'Designacao' => 'Outro',
            'NIF' => '91' . str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT),
            'Cedula' => "CED-{$suffix}-" . random_int(1000, 9999),
            'Endereco_completo' => "Rua {$suffix}",
            'Provincia' => 'Luanda',
            'Cidade' => 'Luanda',
            'Email' => "empresa-{$suffix}@example.test",
            'Contacto_movel' => '923000000',
            'Contacto_fixo' => '222000000',
            'Sigla' => strtoupper(substr($suffix, 0, 3)),
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return Empresa::query()->findOrFail($id);
    }

    private function createCustomer(Empresa $empresa, User $user, string $suffix): Customer
    {
        $id = DB::table('customers')->insertGetId($this->onlyExistingColumns('customers', [
            'CustomerID' => "CUST-{$suffix}",
            'AccountID' => "ACC-{$suffix}",
            'CustomerTaxID' => "NIF-{$suffix}",
            'CompanyName' => "Cliente {$suffix}",
            'Telephone' => '923000000',
            'Email' => "cliente-{$suffix}@example.test",
            'Website' => null,
            'SelfBillingIndicator' => 0,
            'CustomerType' => 'Empresa',
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $customer = Customer::query()->findOrFail($id);

        if (Schema::hasTable('customers_empresas')) {
            $customer->empresas()->syncWithoutDetaching([$empresa->id]);
        }

        return $customer;
    }

    private function createProcesso(Customer $customer, string $suffix): Processo
    {
        $id = DB::table('processos')->insertGetId($this->onlyExistingColumns('processos', [
            'NrProcesso' => "PROC-{$suffix}-" . random_int(1000, 9999),
            'Descricao' => "Processo {$suffix}",
            'DataAbertura' => now()->toDateString(),
            'TipoProcesso' => 1,
            'Situacao' => 'Em processamento',
            'Estado' => 'Aberto',
            'customer_id' => $customer->id,
            'user_id' => $customer->user_id,
            'empresa_id' => $customer->empresa_id,
            'forma_pagamento' => 'RD',
            'codigo_banco' => '001',
            'Cambio' => 1,
            'fob_total' => 100,
            'frete' => 10,
            'seguro' => 5,
            'cif' => 115,
            'ValorAduaneiro' => 115,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return Processo::query()->findOrFail($id);
    }

    private function createLicenciamento(Customer $customer, string $suffix): Licenciamento
    {
        return Licenciamento::query()->create($this->onlyExistingColumns('licenciamentos', [
            'codigo_licenciamento' => "LIC-{$suffix}-" . random_int(1000, 9999),
            'estancia_id' => $this->ensureEstancia(),
            'cliente_id' => $customer->id,
            'exportador_id' => $this->ensureExportador($customer, $suffix),
            'empresa_id' => $customer->empresa_id,
            'referencia_cliente' => "REF-{$suffix}",
            'factura_proforma' => "FP-{$suffix}",
            'descricao' => "Licenciamento {$suffix}",
            'moeda' => 'AOA',
            'tipo_declaracao' => 1,
            'tipo_transporte' => 1,
            'porto_entrada' => 'LAD',
            'peso_bruto' => 100,
            'adicoes' => 1,
            'metodo_avaliacao' => 'M1',
            'codigo_volume' => 'CX',
            'forma_pagamento' => 'RD',
            'codigo_banco' => '001',
            'fob_total' => 100,
            'frete' => 10,
            'seguro' => 5,
            'cif' => 115,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    private function ensureEstancia(): int
    {
        if (! Schema::hasTable('estancias')) {
            return 1;
        }

        return DB::table('estancias')->insertGetId($this->onlyExistingColumns('estancias', [
            'cod_estancia' => 'EST' . random_int(100, 999),
            'desc_estancia' => 'Estancia Portal',
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    private function ensureExportador(Customer $customer, string $suffix): int
    {
        if (! Schema::hasTable('exportadors')) {
            return 1;
        }

        return DB::table('exportadors')->insertGetId($this->onlyExistingColumns('exportadors', [
            'ExportadorID' => "EXP-{$suffix}-" . random_int(1000, 9999),
            'AccountID' => "ACC-EXP-{$suffix}",
            'ExportadorTaxID' => "NIF-EXP-{$suffix}",
            'Exportador' => "Exportador {$suffix}",
            'Endereco' => "Rua Exportador {$suffix}",
            'Telefone' => '923000000',
            'Email' => "exportador-{$suffix}@example.test",
            'Pais' => 1,
            'Cidade' => 'Luanda',
            'user_id' => $customer->user_id,
            'empresa_id' => $customer->empresa_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    private function onlyExistingColumns(string $table, array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(Schema::getColumnListing($table)));
    }
}
