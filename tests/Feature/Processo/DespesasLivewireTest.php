<?php

namespace Tests\Feature\Processo;

use App\Livewire\Processo\Despesas;
use App\Models\EmolumentoTarifa;
use App\Models\Processo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DespesasLivewireTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_mount_hydrates_manual_values_without_recalculating_taxes(): void
    {
        [$user, $empresa] = $this->createTenant('DESP-A');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'DESP-A');
        $exportador = $this->createExportador($empresa, $user, 'DESP-A');
        $processo = $this->createProcessoWithoutEvents($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId, [
            'ValorAduaneiro' => 10000,
        ]);

        DB::table('emolumento_tarifas')->insert([
            'processo_id' => $processo->id,
            'honorario' => 100,
            'iva_aduaneiro' => 7,
            'impostoEstatistico' => 8,
            'honorario_iva' => 9,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::test(Despesas::class, ['processo' => $processo])
            ->assertSet('form.honorario', 100.0)
            ->assertSet('form.iva_aduaneiro', 7.0)
            ->assertSet('form.impostoEstatistico', 8.0)
            ->assertSet('form.honorario_iva', 9.0)
            ->assertSet('totais.impostos', 24.0);
    }

    public function test_renders_explicit_expense_fields_without_dynamic_schema(): void
    {
        [$user, $empresa] = $this->createTenant('DESP-UI');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'DESP-UI');
        $exportador = $this->createExportador($empresa, $user, 'DESP-UI');
        $processo = $this->createProcessoWithoutEvents($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);

        Livewire::test(Despesas::class, ['processo' => $processo])
            ->assertSee('Taxas Portuárias')
            ->assertSee('Transporte e Logística')
            ->assertSee('Taxas Aduaneiras')
            ->assertSee('Serviços Profissionais')
            ->assertSee('Impostos e Taxas')
            ->assertSeeHtml('name="despesa_porto"')
            ->assertSeeHtml('name="despesa_emolumentos"')
            ->assertSeeHtml('name="despesa_juros_mora"')
            ->assertSeeHtml('name="despesa_multas"')
            ->assertSeeHtml('name="despesa_orgaos_ofiais"');
    }

    public function test_changing_honorario_does_not_change_honorario_iva(): void
    {
        [$user, $empresa] = $this->createTenant('DESP-B');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'DESP-B');
        $exportador = $this->createExportador($empresa, $user, 'DESP-B');
        $processo = $this->createProcessoWithoutEvents($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);

        Livewire::test(Despesas::class, ['processo' => $processo])
            ->set('form.honorario_iva', '12,50')
            ->set('form.honorario', '1.500,00')
            ->assertSet('form.honorario', 1500.0)
            ->assertSet('form.honorario_iva', 12.5);
    }

    public function test_valor_aduaneiro_does_not_fill_tax_fields(): void
    {
        [$user, $empresa] = $this->createTenant('DESP-C');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'DESP-C');
        $exportador = $this->createExportador($empresa, $user, 'DESP-C');
        $processo = $this->createProcessoWithoutEvents($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId, [
            'ValorAduaneiro' => 25000,
        ]);

        Livewire::test(Despesas::class, ['processo' => $processo])
            ->assertSet('form.iva_aduaneiro', 0.0)
            ->assertSet('form.impostoEstatistico', 0.0)
            ->assertSet('form.honorario_iva', 0.0)
            ->assertSet('totalGeral', 0.0);
    }

    public function test_save_preserves_manual_values_and_totals_are_calculated(): void
    {
        [$user, $empresa] = $this->createTenant('DESP-D');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'DESP-D');
        $exportador = $this->createExportador($empresa, $user, 'DESP-D');
        $processo = $this->createProcessoWithoutEvents($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId, [
            'ValorAduaneiro' => 30000,
        ]);

        Livewire::test(Despesas::class, ['processo' => $processo])
            ->set('form.porto', '100,00')
            ->set('form.frete', '50,00')
            ->set('form.direitos', '25,00')
            ->set('form.emolumentos', '7,00')
            ->set('form.juros_mora', '3,00')
            ->set('form.multas', '2,00')
            ->set('form.lmc', '10,00')
            ->set('form.honorario', '40,00')
            ->set('form.orgaos_ofiais', '6,00')
            ->set('form.iva_aduaneiro', '5,00')
            ->assertSet('totais.portuarias', 100.0)
            ->assertSet('totais.transporte', 50.0)
            ->assertSet('totais.aduaneiras', 37.0)
            ->assertSet('totais.inspecoes', 10.0)
            ->assertSet('totais.servicos', 46.0)
            ->assertSet('totais.impostos', 5.0)
            ->assertSet('totalGeral', 248.0)
            ->call('save')
            ->assertHasNoErrors();

        $tarifa = EmolumentoTarifa::query()->where('processo_id', $processo->id)->firstOrFail();

        $this->assertSame(100.0, (float) $tarifa->porto);
        $this->assertSame(50.0, (float) $tarifa->frete);
        $this->assertSame(25.0, (float) $tarifa->direitos);
        $this->assertSame(7.0, (float) $tarifa->emolumentos);
        $this->assertSame(3.0, (float) $tarifa->juros_mora);
        $this->assertSame(2.0, (float) $tarifa->multas);
        $this->assertSame(10.0, (float) $tarifa->lmc);
        $this->assertSame(40.0, (float) $tarifa->honorario);
        $this->assertSame(6.0, (float) $tarifa->orgaos_ofiais);
        $this->assertSame(5.0, (float) $tarifa->iva_aduaneiro);
        $this->assertSame(0.0, (float) $tarifa->impostoEstatistico);
        $this->assertSame(0.0, (float) $tarifa->honorario_iva);
        $this->assertNull($tarifa->guia_fiscal);
    }

    public function test_reset_to_defaults_zeros_fields_and_totals(): void
    {
        [$user, $empresa] = $this->createTenant('DESP-E');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'DESP-E');
        $exportador = $this->createExportador($empresa, $user, 'DESP-E');
        $processo = $this->createProcessoWithoutEvents($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);

        Livewire::test(Despesas::class, ['processo' => $processo])
            ->set('form.porto', '100,00')
            ->set('form.iva_aduaneiro', '14,00')
            ->call('resetToDefaults')
            ->assertSet('form.porto', 0)
            ->assertSet('form.iva_aduaneiro', 0)
            ->assertSet('totais.portuarias', 0.0)
            ->assertSet('totais.impostos', 0.0)
            ->assertSet('totalGeral', 0.0);
    }

    public function testEmolumentoTarifaModelSaveDoesNotOverwriteManualValues(): void
    {
        [$user, $empresa] = $this->createTenant('DESP-F');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'DESP-F');
        $exportador = $this->createExportador($empresa, $user, 'DESP-F');
        $processo = $this->createProcessoWithoutEvents($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId, [
            'ValorAduaneiro' => 50000,
        ]);

        $tarifa = EmolumentoTarifa::query()->create([
            'processo_id' => $processo->id,
            'honorario' => 200,
            'iva_aduaneiro' => 11,
            'impostoEstatistico' => 22,
            'honorario_iva' => 33,
        ]);

        $tarifa->refresh();

        $this->assertSame(11.0, (float) $tarifa->iva_aduaneiro);
        $this->assertSame(22.0, (float) $tarifa->impostoEstatistico);
        $this->assertSame(33.0, (float) $tarifa->honorario_iva);
        $this->assertNull($tarifa->emolumentos);
        $this->assertNull($tarifa->guia_fiscal);
    }

    private function createProcessoWithoutEvents(...$arguments): Processo
    {
        return Processo::withoutEvents(fn () => $this->createProcesso(...$arguments));
    }
}
