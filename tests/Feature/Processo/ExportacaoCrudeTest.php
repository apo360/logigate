<?php

namespace Tests\Feature\Processo;

use App\Application\Processo\Actions\AtualizarProcessoAction;
use App\Application\Processo\Actions\CriarExportacaoCrudeAction;
use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Domains\Processo\Enums\NaturezaOperacaoProcesso;
use App\Livewire\Processo\ExportacaoCrudeCreate;
use App\Livewire\Processo\ExportacaoCrudeEdit;
use App\Livewire\Processo\ProcessoCreate;
use App\Livewire\Processo\ProcessoEdit;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\Support\IsolatedDatabaseTestCase;

class ExportacaoCrudeTest extends IsolatedDatabaseTestCase
{
    use ProcessoTestFixtures;

    private function scenario(): array
    {
        [$user, $empresa] = $this->createTenant('CRUD');
        [$estancia, $importacao] = $this->createLookupData();
        $exportacao = DB::table('regiao_aduaneiras')->insertGetId([
            'codigo' => '1', 'abrev' => 'EX', 'descricao' => 'Exportação Definitiva Teste CRUD',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $customer = $this->createCustomer($empresa, $user, 'CRUD');
        $exportador = $this->createExportador($empresa, $user, 'CRUD');
        $this->grantTenantPermissions($user, ['processos.create', 'processos.view', 'processos.update']);
        $this->signInTenant($user);

        return [
            'customer_id' => $customer->id, 'exportador_id' => $exportador->id,
            'estancia_id' => $estancia, 'TipoProcesso' => (string) $exportacao,
            'empresa_id' => $empresa->id, 'user_id' => $user->id,
            'forma_pagamento' => 'RD', 'codigo_banco' => '0040', 'Estado' => 'Aberto',
            'DataAbertura' => now()->toDateString(), '_importacao' => (string) $importacao,
        ];
    }

    private function createCrude(array $data): Processo
    {
        return app(CriarExportacaoCrudeAction::class)->execute(CriarProcessoDTO::fromArray($data));
    }

    private function rejectValidation(callable $operation, string $field): void
    {
        try {
            $operation();
            $this->fail('Expected a validation error for ' . $field);
        } catch (ValidationException $error) {
            $this->assertArrayHasKey($field, $error->errors());
        }
    }

    public function test_general_and_crude_forms_use_real_regimes_and_independent_fields(): void
    {
        $this->scenario();
        $general = Livewire::test(ProcessoCreate::class)->assertDontSeeHtml('wire:model="rsm_num"');
        $tipo = $general->get('tipoProcessoOptions')->firstWhere('id', $general->get('TipoProcesso'));
        $this->assertSame('IM', $tipo->abrev);
        $this->assertSame('4', (string) $tipo->codigo);

        $crude = Livewire::test(ExportacaoCrudeCreate::class)->assertSeeHtml('wire:model="rsm_num"');
        $tipo = $crude->get('tipoProcessoOptions')->firstWhere('id', $crude->get('TipoProcesso'));
        $this->assertSame('EX', $tipo->abrev);
        $this->assertSame('1', (string) $tipo->codigo);
        $this->assertTrue($crude->get('tipoProcessoOptions')->every(fn ($tipo) => in_array($tipo->abrev, ['EX', 'EXS', 'EXV'], true)));
    }

    public function test_crude_form_persists_classification_and_specific_fields_in_processos(): void
    {
        $data = $this->scenario();
        $component = Livewire::test(ExportacaoCrudeCreate::class)->set(array_diff_key($data, array_flip(['_importacao', 'empresa_id', 'user_id'])))
            ->set('quantidade_barris', '100')->set('valor_barril_usd', '75.50')
            ->set('num_deslocacoes', '2')->set('rsm_num', 'RSM-CRUD')
            ->set('guia_exportacao', 'GUIA-CRUD')->set('certificado_origem', 'CERT-CRUD')
            ->call('save')->assertHasNoErrors();
        $processo = Processo::query()->where('empresa_id', $data['empresa_id'])->sole();
        $component->assertRedirect(route('processos.show', $processo));
        $this->assertSame('exportacao_crude', $processo->natureza_operacao);
        $this->assertSame('GUIA-CRUD', $processo->guia_exportacao);
        $this->assertSame('RSM-CRUD', $processo->rsm_num);
        $this->assertSame(100, (int) $processo->quantidade_barris);
        $this->assertSame(75.5, (float) $processo->valor_barril_usd);
        $this->assertNotEmpty($processo->NrProcesso);
    }

    public function test_crude_opening_allows_loading_and_documents_to_be_completed_later(): void
    {
        $data = $this->scenario();
        $processo = $this->createCrude($data);
        $this->assertTrue($processo->isExportacaoCrude());
        $this->assertNull($processo->certificado_origem);
        $this->assertNull($processo->data_carregamento);
    }

    public function test_barrel_count_preserves_zero_and_never_truncates_fractional_input(): void
    {
        $data = $this->scenario();
        $this->assertSame(0, CriarProcessoDTO::fromArray($data + ['quantidade_barris' => 0])->quantidadeBarris);
        $this->rejectValidation(fn () => CriarProcessoDTO::fromArray($data + ['quantidade_barris' => '10.5']), 'quantidade_barris');
        $this->rejectValidation(fn () => AtualizarProcessoDTO::fromArray(['id' => 1, 'quantidade_barris' => '10.5']), 'quantidade_barris');
    }

    public function test_crude_terminal_process_keeps_existing_lifecycle_protection(): void
    {
        $processo = $this->createCrude($this->scenario() + ['rsm_num' => 'LOCKED']);
        DB::table('processos')->where('id', $processo->id)->update(['Estado' => 'Finalizado']);
        app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray(['id' => $processo->id, 'observacoes' => 'Nota final']));
        $this->assertSame('Nota final', $processo->refresh()->observacoes);
        $this->expectException(\InvalidArgumentException::class);
        app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray(['id' => $processo->id, 'rsm_num' => 'CHANGED']));
    }

    public function test_http_routes_authorize_creation_and_select_the_right_pages(): void
    {
        $this->scenario();
        // Commercial entitlement has its own suite; retain authentication, tenant and policy middleware.
        config(['navigation.route_modules' => []]);
        $this->get(route('processos.exportacao'))->assertOk()->assertSee('Criar exportação CRUD');
        $this->get(route('processos.exportacao-crude.create'))->assertOk()->assertSee('Dados de exportação CRUD');
        $user = auth()->user();
        \App\Support\CompanyRbac::within((int) $user->empresas()->sole()->id, fn () => $user->revokePermissionTo('processos.create'));
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->get(route('processos.exportacao'))->assertForbidden();
        $this->get(route('processos.exportacao-crude.create'))->assertForbidden();
    }

    public function test_general_creation_rejects_petroleum_fields_on_frontend_and_backend(): void
    {
        $data = $this->scenario();
        Livewire::test(ProcessoCreate::class)
            ->set(array_diff_key($data, array_flip(['_importacao', 'empresa_id', 'user_id'])))
            ->set('rsm_num', 'FORGED')->call('save')->assertHasErrors('rsm_num');
        $this->rejectValidation(fn () => app(CriarProcessoAction::class)->execute(
            CriarProcessoDTO::fromArray($data + ['quantidade_barris' => 100])
        ), 'quantidade_barris');
        $this->assertSame(0, DB::table('processos')->where('empresa_id', $data['empresa_id'])->count());
    }

    public function test_shared_creation_enforces_crude_regime_and_validation(): void
    {
        $data = $this->scenario();
        $invalid = array_replace($data, ['TipoProcesso' => $data['_importacao'], 'natureza_operacao' => 'exportacao_crude']);
        $this->rejectValidation(fn () => app(CriarProcessoAction::class)->execute(CriarProcessoDTO::fromArray($invalid)), 'TipoProcesso');
        Livewire::test(ExportacaoCrudeCreate::class)
            ->set(array_diff_key($invalid, array_flip(['_importacao', 'empresa_id', 'user_id', 'natureza_operacao'])))
            ->set('quantidade_barris', -1)->call('save')->assertHasErrors(['TipoProcesso', 'quantidade_barris']);
    }

    public function test_crude_update_keeps_nature_and_can_clear_optional_document(): void
    {
        $data = $this->scenario();
        $processo = $this->createCrude($data + ['rsm_num' => 'OLD', 'certificado_origem' => 'OLD-CERT']);
        Livewire::test(ExportacaoCrudeEdit::class, ['processo' => $processo])
            ->set('rsm_num', 'NEW')->set('certificado_origem', '')
            ->call('update')->assertHasNoErrors()->assertRedirect(route('processos.show', $processo));
        $this->assertSame('NEW', $processo->refresh()->rsm_num);
        $this->assertNull($processo->certificado_origem);
        $this->assertSame('exportacao_crude', $processo->natureza_operacao);
    }

    public function test_shared_update_rejects_incompatible_regime_and_nature_changes(): void
    {
        $data = $this->scenario();
        $processo = $this->createCrude($data);
        $this->rejectValidation(fn () => app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray([
            'id' => $processo->id, 'TipoProcesso' => $data['_importacao'],
        ])), 'TipoProcesso');
        $this->rejectValidation(fn () => app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray([
            'id' => $processo->id, 'natureza_operacao' => 'geral',
        ])), 'natureza_operacao');
        $this->assertSame('exportacao_crude', $processo->refresh()->natureza_operacao);
        $this->assertSame($data['TipoProcesso'], (string) $processo->TipoProcesso);
    }

    public function test_legacy_export_data_stays_accessible_and_unclassified(): void
    {
        $data = $this->scenario();
        $id = DB::table('processos')->insertGetId([
            'NrProcesso' => 'LEGACY-CRUD-TEST', 'customer_id' => $data['customer_id'], 'exportador_id' => $data['exportador_id'],
            'user_id' => $data['user_id'], 'empresa_id' => $data['empresa_id'], 'estancia_id' => $data['estancia_id'],
            'TipoProcesso' => $data['TipoProcesso'], 'Estado' => 'Aberto', 'DataAbertura' => $data['DataAbertura'],
            'forma_pagamento' => 'RD', 'codigo_banco' => '0040', 'rsm_num' => 'LEGACY-RSM',
        ]);
        $processo = Processo::query()->findOrFail($id);
        Livewire::test(ProcessoEdit::class, ['processo' => $processo])
            ->assertSee('anteriores à classificação por natureza')->assertSet('rsm_num', 'LEGACY-RSM')
            ->set('RefCliente', 'EDIT-LEGACY')->call('update')->assertHasNoErrors();
        $this->assertNull($processo->refresh()->natureza_operacao);
        $this->assertSame('LEGACY-RSM', $processo->rsm_num);
    }

    public function test_crude_edit_rejects_another_company(): void
    {
        $processo = $this->createCrude($this->scenario());
        [$other] = $this->createTenant('CRUD-OTHER');
        $this->grantTenantPermissions($other, ['processos.update']);
        $this->signInTenant($other);
        Livewire::test(ExportacaoCrudeEdit::class, ['processo' => $processo])->assertForbidden();
    }

    public function test_crude_create_requires_create_permission(): void
    {
        [$user] = $this->createTenant('CRUD-NOPERM');
        $this->signInTenant($user);
        Livewire::test(ExportacaoCrudeCreate::class)->assertForbidden();
    }

    public function test_menu_migration_reuses_existing_exportacao_entry(): void
    {
        $menu = DB::table('menus')->where('menu_name', 'Exportação')->first();
        $this->assertNotNull($menu);
        $before = DB::table('menus')->where('id', '<>', $menu->id)->orderBy('id')->get()->toJson();
        $count = DB::table('menus')->count();
        DB::table('menus')->where('id', $menu->id)->update(['route' => 'processos.create', 'slug' => 'processos.create']);
        $migration = require database_path('migrations/2026_10_17_100001_link_existing_exportacao_menu.php');
        $migration->up();
        $migration->up();
        $this->assertSame('processos.exportacao', DB::table('menus')->where('id', $menu->id)->value('route'));
        $this->assertSame($count, DB::table('menus')->count());
        $this->assertSame($before, DB::table('menus')->where('id', '<>', $menu->id)->orderBy('id')->get()->toJson());
    }

    public function test_pages_route_to_choice_and_appropriate_edit_component(): void
    {
        $data = $this->scenario();
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $hub = view('processos.exportacao.index')->render();
        $this->assertStringContainsString(route('processos.exportacao-crude.create'), $hub);
        $this->assertStringContainsString('regime=exportacao', $hub);
        $create = view('processos.exportacao-crude.create')->render();
        $this->assertStringContainsString('processo.exportacao-crude-create', $create);
        $processo = $this->createCrude($data);
        $edit = view('processos.edit', ['processo' => $processo])->render();
        $this->assertStringContainsString('processo.exportacao-crude-edit', $edit);
        if (getenv('PROCESSO_CRUDE_BROWSER_FIXTURE')) {
            @mkdir(storage_path('app/processo-crude-qa'), 0777, true);
            file_put_contents(storage_path('app/processo-crude-qa/hub.html'), $hub);
            file_put_contents(storage_path('app/processo-crude-qa/create.html'), $create);
            file_put_contents(storage_path('app/processo-crude-qa/edit.html'), $edit);
        }
    }
}
