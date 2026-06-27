<?php

namespace Tests\Feature\Licenciamento;

use App\Livewire\Licenciamento\LiicenciamentoShow;
use App\Models\Mercadoria;
use App\Models\MercadoriaAgrupada;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class LicenciamentoShowActionsTest extends TestCase
{
    use DatabaseTransactions;
    use LicenciamentoTestSupport;
    use ProcessoTestFixtures;

    public function test_show_renders_quick_actions_without_stale_links(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-ACTIONS');
        $this->grantShowPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'SHOW-ACTIONS');

        $this->actingAs($user);

        $component = Livewire::test(LiicenciamentoShow::class, ['licenciamento' => $licenciamento]);

        $component
            ->assertSee('Ações Rápidas')
            ->assertSee('Editar Licenciamento')
            ->assertSee('Gerir Mercadorias')
            ->assertSee('Gerar TXT')
            ->assertSee('Constituir Processo')
            ->assertSee('Duplicar Licenciamento')
            ->assertDontSee('href="#"', false)
            ->assertDontSee(Route::has('gerar.processo') ? route('gerar.processo', $licenciamento->id) : 'licenciamentos/gerarProcesso/' . $licenciamento->id, false)
            ->assertDontSee(Route::has('licenciamentos.duplicar') ? route('licenciamentos.duplicar', $licenciamento->id) : 'licenciamentos/duplicar/' . $licenciamento->id, false);
    }

    public function test_manage_mercadorias_action_selects_mercadorias_area(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-MERC');
        $this->grantShowPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'SHOW-MERC');

        $this->actingAs($user);

        Livewire::test(LiicenciamentoShow::class, ['licenciamento' => $licenciamento])
            ->call('abrirMercadorias')
            ->assertSet('mostrarMercadorias', true)
            ->assertDispatched('licenciamento-show-tab')
            ->assertSee('Mercadorias');
    }

    public function test_show_renders_operational_readiness_checklist_and_financial_summary(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-READY');
        $this->grantShowPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'SHOW-READY');

        $this->actingAs($user);

        Livewire::test(LiicenciamentoShow::class, ['licenciamento' => $licenciamento])
            ->assertSee('Prontidão do Licenciamento')
            ->assertSee('Checklist operacional')
            ->assertSee('Resumo financeiro e aduaneiro')
            ->assertSee('Cliente associado')
            ->assertSee('FOB')
            ->assertSee('Códigos aduaneiros');
    }

    public function test_licenciamento_without_mercadorias_shows_alerts_and_action_blockers(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-NO-MERC');
        $this->grantShowPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'SHOW-NO-MERC');

        $this->actingAs($user);

        Livewire::test(LiicenciamentoShow::class, ['licenciamento' => $licenciamento])
            ->assertSee('Sem mercadorias associadas')
            ->assertSee('Não está pronto para Gerar TXT:')
            ->assertSee('Não está pronto para Constituir Processo:')
            ->assertSet('prontoParaTxt', false)
            ->assertSet('prontoParaProcesso', false)
            ->call('gerarTxt')
            ->assertDispatched('toast')
            ->call('constituirProcesso')
            ->assertDispatched('toast');

        $this->assertDatabaseHas('licenciamentos', [
            'id' => $licenciamento->id,
            'txt_gerado' => $licenciamento->txt_gerado,
        ]);

        if (Schema::hasTable('proc_licen_sales')) {
            $this->assertDatabaseMissing('proc_licen_sales', [
                'licenciamento_id' => $licenciamento->id,
            ]);
        }
    }

    public function test_licenciamento_with_mercadorias_removes_missing_mercadorias_alert(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-WITH-MERC');
        $this->grantShowPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'SHOW-WITH-MERC');
        $this->createMercadoriaForLicenciamento($licenciamento->id);

        $this->actingAs($user);

        Livewire::test(LiicenciamentoShow::class, ['licenciamento' => $licenciamento->refresh()])
            ->assertDontSee('Sem mercadorias associadas')
            ->assertSee('Mercadorias associadas');
    }

    public function test_cif_inconsistente_gera_alerta(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-CIF');
        $this->grantShowPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'SHOW-CIF');
        $licenciamento->forceFill(['cif' => 999])->save();

        $this->actingAs($user);

        Livewire::test(LiicenciamentoShow::class, ['licenciamento' => $licenciamento->refresh()])
            ->assertSee('CIF inconsistente')
            ->assertSee('O CIF deve corresponder a FOB + Frete + Seguro.');
    }

    public function test_timeline_renderiza_eventos_basicos(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-TIME');
        $this->grantShowPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'SHOW-TIME');
        $this->createMercadoriaForLicenciamento($licenciamento->id);
        $licenciamento->forceFill(['txt_gerado' => 1])->save();

        $this->actingAs($user);

        Livewire::test(LiicenciamentoShow::class, ['licenciamento' => $licenciamento->refresh()])
            ->assertSee('Timeline')
            ->assertSee('Licenciamento criado')
            ->assertSee('Mercadorias associadas')
            ->assertSee('TXT gerado');
    }

    public function test_faturas_sidebar_renders_empty_state(): void
    {
        [$user, $empresa] = $this->createTenant('SHOW-FAT-EMPTY');
        $this->grantShowPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'SHOW-FAT-EMPTY');

        $this->actingAs($user);

        Livewire::test(LiicenciamentoShow::class, ['licenciamento' => $licenciamento])
            ->assertSee('Sem faturas associadas.');
    }

    public function test_faturas_sidebar_handles_missing_related_invoice_and_process(): void
    {
        if (! Schema::hasTable('proc_licen_sales')) {
            $this->markTestSkipped('proc_licen_sales table is not available.');
        }

        [$user, $empresa] = $this->createTenant('SHOW-FAT-SAFE');
        $this->grantShowPermissions($user);
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'SHOW-FAT-SAFE');

        Schema::disableForeignKeyConstraints();

        try {
            DB::table('proc_licen_sales')->insert([
                'empresa_id' => $empresa->id,
                'licenciamento_id' => $licenciamento->id,
                'processo_id' => null,
                'fatura_id' => 1,
                'status_fatura' => 'emitida',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->actingAs($user);

        Livewire::test(LiicenciamentoShow::class, ['licenciamento' => $licenciamento->refresh()])
            ->assertSee('Fatura:')
            ->assertSee('Emitida')
            ->assertSee('Não informada');
    }

    public function test_cross_tenant_user_cannot_mount_show_livewire_component(): void
    {
        [$tenantAUser] = $this->createTenant('SHOW-X-A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('SHOW-X-B');
        $this->grantShowPermissions($tenantAUser);
        $licenciamentoB = $this->createLicenciamentoFor($tenantBEmpresa, $tenantBUser, 'SHOW-X-B');

        $this->assertFalse($tenantAUser->can('view', $licenciamentoB));

        $this->actingAs($tenantAUser)
            ->get(route('licenciamentos.show', $licenciamentoB))
            ->assertNotFound();
    }

    private function createMercadoriaForLicenciamento(int $licenciamentoId): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            $mercadoria = Mercadoria::query()->create([
                'Fk_Importacao' => 1,
                'licenciamento_id' => $licenciamentoId,
                'codigo_aduaneiro' => '0203.11.00',
                'Descricao' => 'Mercadoria operacional',
                'Quantidade' => 2,
                'Unidade' => 'Kg',
                'Peso' => 10,
                'preco_unitario' => 50,
                'preco_total' => 100,
            ]);

            MercadoriaAgrupada::query()->create([
                'licenciamento_id' => $licenciamentoId,
                'codigo_aduaneiro' => '0203.11.00',
                'quantidade_total' => 2,
                'peso_total' => 10,
                'preco_total' => 100,
                'mercadorias_ids' => json_encode([$mercadoria->id]),
            ]);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function grantShowPermissions(User $user): void
    {
        $this->grantLicenciamentoPermissions($user, [
            'licenciamentos.view',
            'licenciamentos.create',
            'licenciamentos.update',
            'licenciamentos.delete',
            'mercadorias.view',
        ]);
    }
}
