<?php

namespace Tests\Feature\Mercadoria;

use App\Application\Mercadoria\Actions\{AtualizarMercadoriaAction, CriarMercadoriaAction, ExcluirMercadoriaAction};
use App\Application\Mercadoria\DTOs\MercadoriaData;
use App\Application\Mercadoria\Services\MercadoriaAgrupamentoService;
use App\Application\Licenciamento\Actions\{ConstituirProcessoAction, DuplicarLicenciamentoAction};
use App\Application\Arquivo\Services\FileStorageService;
use App\Models\{Mercadoria, MercadoriaAgrupada, PautaAduaneira};
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\Support\IsolatedDatabaseTestCase;

class MercadoriaAuditContractsTest extends IsolatedDatabaseTestCase
{
    use \Tests\Feature\Processo\ProcessoTestFixtures;
    use \Tests\Feature\Licenciamento\LicenciamentoTestSupport;

    private function scenario(): array
    {
        [$user, $empresa] = $this->createTenant('MERC-AUDIT');
        $license = $this->createLicenciamentoFor($empresa, $user, 'MERC-AUDIT');
        [$estancia, $tipo] = $this->createLookupData();
        $process = $this->createProcesso($empresa, $user, $license->cliente, $license->exportador, $estancia, $tipo);
        $this->actingAs($user);
        TenantContext::setEmpresa($user, $empresa);
        $permissions = ['mercadorias.create', 'mercadorias.update', 'mercadorias.delete', 'licenciamentos.view', 'licenciamentos.create', 'licenciamentos.update', 'processos.create'];
        foreach ($permissions as $permission) { Permission::findOrCreate($permission, 'web'); }
        $user->syncPermissions($permissions);
        PautaAduaneira::firstOrCreate(['codigo' => '0203.11.00'], ['descricao' => 'Carnes', 'uq' => 'kg', 'rg' => 0, 'sadc' => 0, 'ua' => 0, 'iva' => 0, 'ieq' => 0, 'requisitos' => '0', 'observacao' => '0']);
        return [$license, $process];
    }

    private function data($license, ?int $id = null, float $price = 100): MercadoriaData
    {
        return MercadoriaData::fromLivewire(['codigo_aduaneiro' => '0203.11.00', 'descricao' => 'Teste', 'unidade' => 'Kg', 'quantidade' => 1, 'peso' => 2, 'preco_unitario' => $price], 'licenciamento', $license->id, $id);
    }

    public function test_shared_items_keep_both_links_and_totals_without_changing_declared_headers(): void
    {
        [$license, $process] = $this->scenario();
        $item = app(CriarMercadoriaAction::class)->execute($this->data($license));
        $item->update(['Fk_Importacao' => $process->id]);
        app(MercadoriaAgrupamentoService::class)->addOrUpdate($item);
        $updated = app(AtualizarMercadoriaAction::class)->execute($this->data($license, $item->id, 150));
        self::assertSame($process->id, (int) $updated->Fk_Importacao);
        self::assertSame($license->id, (int) $updated->licenciamento_id);
        self::assertSame(150.0, $license->totaisMercadorias()['fob']);
        self::assertSame(150.0, $process->totaisMercadorias()['fob']);
        self::assertSame(165.0, $process->totaisMercadorias()['cif']);
        self::assertSame(2.0, $process->totaisMercadorias()['peso']);
        self::assertSame(100.0, (float) $license->refresh()->fob_total);
        self::assertSame(100.0, (float) $process->refresh()->fob_total);
        app(ExcluirMercadoriaAction::class)->execute($item->id, 'licenciamento', $license->id);
        self::assertSame(0.0, $license->totaisMercadorias()['fob']);
        self::assertSame(0.0, $process->totaisMercadorias()['fob']);
        self::assertSame(0, MercadoriaAgrupada::where('licenciamento_id', $license->id)->count());
        self::assertSame(0, MercadoriaAgrupada::where('processo_id', $process->id)->count());
    }

    public function test_repeating_grouping_does_not_duplicate_values_or_additions(): void
    {
        [$license, $process] = $this->scenario();
        $item = app(CriarMercadoriaAction::class)->execute($this->data($license));
        $item->update(['Fk_Importacao' => $process->id]);
        foreach (range(1, 3) as $_) { app(MercadoriaAgrupamentoService::class)->addOrUpdate($item); }
        foreach (['licenciamento_id' => $license->id, 'processo_id' => $process->id] as $key => $id) {
            $group = MercadoriaAgrupada::where($key, $id)->sole();
            self::assertSame(100.0, (float) $group->preco_total);
            self::assertSame([$item->id], json_decode($group->mercadorias_ids, true));
            self::assertSame([$item->id], $group->mercadoriasQuery()->pluck('id')->all());
        }
        self::assertSame(1, (int) $license->refresh()->adicoes);
    }

    public function test_new_items_in_a_converted_license_share_the_process_and_terminal_process_blocks_creation(): void
    {
        [$license, $process] = $this->scenario();
        $first = app(CriarMercadoriaAction::class)->execute($this->data($license));
        $first->update(['Fk_Importacao' => $process->id]);
        $second = app(CriarMercadoriaAction::class)->execute($this->data($license));
        self::assertSame($process->id, (int) $second->Fk_Importacao);
        self::assertSame(200.0, $process->totaisMercadorias()['fob']);
        DB::table('processos')->where('id', $process->id)->update(['Estado' => 'Finalizado']);
        $this->expectException(\InvalidArgumentException::class);
        app(CriarMercadoriaAction::class)->execute($this->data($license));
    }

    public function test_exchange_calculation_does_not_default_to_one_and_ignores_forged_derived_values(): void
    {
        [, $process] = $this->scenario();
        Permission::findOrCreate('processos.update', 'web');
        auth()->user()->givePermissionTo('processos.update');
        $action = app(\App\Application\Processo\Actions\AtualizarProcessoAction::class);
        $updated = $action->execute(\App\Application\Processo\DTOs\AtualizarProcessoDTO::fromArray(['id' => $process->id, 'Cambio' => 2, 'cif' => 999, 'ValorAduaneiro' => 999]));
        self::assertSame(115.0, (float) $updated->cif);
        self::assertSame(230.0, (float) $updated->ValorAduaneiro);
        $pending = $action->execute(\App\Application\Processo\DTOs\AtualizarProcessoDTO::fromArray(['id' => $process->id, 'Cambio' => null]));
        self::assertNull($pending->ValorAduaneiro);
        self::assertNull($pending->totaisMercadorias()['valor_aduaneiro']);
        self::assertContains('O campo Cambio é obrigatório.', app(\App\Domains\Processo\Services\ProcessoFinalizacaoRules::class)->validar($pending));
    }

    public function test_duplication_remaps_items_and_clears_original_process_links(): void
    {
        [$license, $process] = $this->scenario();
        $item = app(CriarMercadoriaAction::class)->execute($this->data($license));
        $item->update(['Fk_Importacao' => $process->id]);
        $license->update(['txt_gerado' => true, 'Nr_factura' => 'ORIGINAL']);
        $copy = app(DuplicarLicenciamentoAction::class)->execute($license);
        $copiedItem = $copy->mercadorias()->sole();
        self::assertNotSame($item->id, $copiedItem->id);
        self::assertNull($copiedItem->Fk_Importacao);
        self::assertNull($copy->Nr_factura);
        self::assertFalse((bool) $copy->txt_gerado);
        self::assertSame([$copiedItem->id], json_decode($copy->mercadoriasAgrupadas()->sole()->mercadorias_ids, true));
        self::assertSame($process->id, (int) $item->refresh()->Fk_Importacao);
    }

    public function test_conversion_creates_draft_with_shared_items_and_pending_exchange_rate(): void
    {
        [$license] = $this->scenario();
        $item = app(CriarMercadoriaAction::class)->execute($this->data($license));
        $license->update(['txt_gerado' => true, 'porto_origem' => 'LAD', 'codigo_banco' => '0040']);
        // Storage is a test double: no external service or S3 request is made.
        $storage = \Mockery::mock(FileStorageService::class);
        $storage->shouldReceive('createDirectory')->once();
        $this->instance(FileStorageService::class, $storage);
        $count = DB::table('proc_licen_sales')->count();
        $action = app(ConstituirProcessoAction::class);
        $process = $action->execute($license);
        self::assertNull($process->Cambio);
        self::assertNull($process->ValorAduaneiro);
        self::assertSame(115.0, (float) $process->cif);
        self::assertSame('Aberto', $process->Estado);
        self::assertSame($process->id, (int) $item->refresh()->Fk_Importacao);
        self::assertSame($license->id, (int) $item->licenciamento_id);
        self::assertSame($process->id, $action->execute($license)->id);
        self::assertSame($count, DB::table('proc_licen_sales')->count());
    }

    public function test_conversion_rechecks_readiness_in_the_action_before_creating_any_process(): void
    {
        [$license] = $this->scenario();
        $before = DB::table('processos')->count();
        try {
            app(ConstituirProcessoAction::class)->execute($license);
            self::fail('An incomplete license cannot be converted.');
        } catch (\InvalidArgumentException $error) {
            self::assertStringContainsString('mercadorias', $error->getMessage());
        }
        self::assertSame($before, DB::table('processos')->count());
    }
}
