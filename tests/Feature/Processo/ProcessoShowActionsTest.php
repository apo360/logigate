<?php

namespace Tests\Feature\Processo;

use App\Application\Processo\Services\ProcessoJasperService;
use App\Livewire\Processo\ProcessoShow;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProcessoShowActionsTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_show_uses_livewire_actions_for_operational_commands(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-ACTIONS');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'SHOW-ACTIONS');
        $exportador = $this->createExportador($empresa, $user, 'SHOW-ACTIONS');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);
        $this->grantProcessoPermissions($user);

        $this->actingAs($user);

        Livewire::test(ProcessoShow::class, ['processo' => $processo])
            ->assertSee('Gerar TXT')
            ->assertSee('Emitir Nota de Despesa')
            ->assertSee('Gerar Extrato de Mercadoria')
            ->assertDontSee('processos.print')
            ->assertDontSee('processos.Extrato_mercadoria')
            ->assertDontSee('href="#"');
    }

    public function test_emitir_nota_despesa_downloads_pdf_from_action(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-NOTA');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'SHOW-NOTA');
        $exportador = $this->createExportador($empresa, $user, 'SHOW-NOTA');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);
        $this->grantProcessoPermissions($user);
        $path = storage_path('app/testing/nota_despesa.pdf');
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, 'pdf');

        $this->instance(ProcessoJasperService::class, new class ($path) extends ProcessoJasperService {
            public function __construct(private readonly string $path)
            {
            }

            public function generatePdf(string $template, string $outputDirectory, string $outputName, array $params = []): string
            {
                return $this->path;
            }
        });

        $this->actingAs($user);

        Livewire::test(ProcessoShow::class, ['processo' => $processo])
            ->call('emitirNotaDespesa')
            ->assertFileDownloaded('nota_despesa_' . $processo->NrProcesso . '.pdf');
    }

    public function test_gerar_extrato_mercadoria_downloads_pdf_from_action(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-EXTRATO');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'SHOW-EXTRATO');
        $exportador = $this->createExportador($empresa, $user, 'SHOW-EXTRATO');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);
        $this->grantProcessoPermissions($user);
        $path = storage_path('app/testing/extrato_mercadoria.pdf');
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, 'pdf');

        $this->instance(ProcessoJasperService::class, new class ($path) extends ProcessoJasperService {
            public function __construct(private readonly string $path)
            {
            }

            public function generatePdf(string $template, string $outputDirectory, string $outputName, array $params = []): string
            {
                return $this->path;
            }
        });

        $this->actingAs($user);

        Livewire::test(ProcessoShow::class, ['processo' => $processo])
            ->call('gerarExtratoMercadoria')
            ->assertFileDownloaded('extrato_mercadoria_' . $processo->NrProcesso . '.pdf');
    }

    public function test_gerar_txt_requires_grouped_mercadorias(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-TXT-BLOCK');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'SHOW-TXT-BLOCK');
        $exportador = $this->createExportador($empresa, $user, 'SHOW-TXT-BLOCK');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);
        $this->grantProcessoPermissions($user);

        $this->actingAs($user);

        Livewire::test(ProcessoShow::class, ['processo' => $processo])
            ->call('gerarTxt')
            ->assertDispatched('toast');
    }

    public function test_gerar_txt_downloads_text_file(): void
    {
        Storage::fake('local');

        [$user, $empresa] = $this->createTenant('SHOW-TXT');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'SHOW-TXT');
        $exportador = $this->createExportador($empresa, $user, 'SHOW-TXT');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);
        $this->grantProcessoPermissions($user);

        DB::table('mercadoria_agrupadas')->insert([
            'codigo_aduaneiro' => '01012100',
            'processo_id' => $processo->id,
            'licenciamento_id' => null,
            'quantidade_total' => 2,
            'peso_total' => 10,
            'preco_total' => 100,
            'mercadorias_ids' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(ProcessoShow::class, ['processo' => $processo->fresh()])
            ->call('gerarTxt')
            ->assertFileDownloaded();
    }

    public function test_cross_tenant_cannot_execute_show_actions(): void
    {
        [$tenantAUser] = $this->createTenant('SHOW-CROSS-A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('SHOW-CROSS-B');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customerB = $this->createCustomer($tenantBEmpresa, $tenantBUser, 'SHOW-CROSS-B');
        $exportadorB = $this->createExportador($tenantBEmpresa, $tenantBUser, 'SHOW-CROSS-B');
        $processoB = $this->createProcesso($tenantBEmpresa, $tenantBUser, $customerB, $exportadorB, $estanciaId, $tipoProcessoId);
        $this->grantProcessoPermissions($tenantAUser);
        $this->grantProcessoPermissions($tenantBUser);

        $this->actingAs($tenantAUser);

        Livewire::test(ProcessoShow::class, ['processo' => $processoB])
            ->assertNotFound();
    }

    private function grantProcessoPermissions($user): void
    {
        $permissions = [
            'processos.view',
            'processos.print',
            'processos.export_xml',
        ];

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
