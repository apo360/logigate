<?php

namespace Tests\Feature\Processo;

use App\Livewire\Processo\ProcessoCreate;
use App\Livewire\Processo\ProcessoEdit;
use App\Livewire\Processo\ExportacaoCrudeCreate;
use App\Livewire\Processo\ProcessoShow;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ProcessoLivewireTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_edit_page_renders_real_components_and_supported_actions(): void
    {
        [$user, $empresa] = $this->createTenant('EDIT-PAGE');
        [$estancia, $tipo] = $this->createLookupData();
        $processo = $this->createProcesso($empresa, $user,
            $this->createCustomer($empresa, $user, 'EDIT-PAGE'),
            $this->createExportador($empresa, $user, 'EDIT-PAGE'), $estancia, $tipo);
        $this->grantTenantPermissions($user, ['processos.update', 'processos.view', 'mercadorias.view', 'processos.print', 'processos.export_xml']);
        $this->signInTenant($user);

        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $html = view('processos.edit', ['processo' => $processo])->render();
        $this->assertStringContainsString('role="tablist"', $html);
        $this->assertStringContainsString('wire:submit="update"', $html);
        $this->assertStringContainsString('wire:submit="save"', $html);
        $this->assertStringContainsString('Emitir factura', $html);
        $this->assertStringNotContainsString('@apply', $html);
        if (getenv('PROCESSO_EDIT_BROWSER_FIXTURE')) {
            @mkdir(storage_path('app/processo-edit-qa'), 0777, true);
            file_put_contents(storage_path('app/processo-edit-qa/page.html'), $html);
        }

        \App\Support\CompanyRbac::within($empresa->id, fn () => $user->revokePermissionTo('mercadorias.view'));
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $restrictedHtml = view('processos.edit', ['processo' => $processo])->render();
        $this->assertStringNotContainsString('id="processo-panel-mercadoria"', $restrictedHtml);
        $this->assertStringContainsString('wire:submit="update"', $restrictedHtml);
    }

    public function test_edit_shows_validation_errors_and_preserves_database_values(): void
    {
        [$user, $empresa] = $this->createTenant('EDIT-ERROR');
        [$estancia, $tipo] = $this->createLookupData();
        $processo = $this->createProcesso($empresa, $user,
            $this->createCustomer($empresa, $user, 'EDIT-ERROR'),
            $this->createExportador($empresa, $user, 'EDIT-ERROR'), $estancia, $tipo);
        $this->grantTenantPermissions($user, ['processos.update']);
        $this->signInTenant($user);

        Livewire::test(ProcessoEdit::class, ['processo' => $processo])
            ->set('codigo_banco', '')
            ->call('update')->assertHasErrors(['codigo_banco' => 'required'])
            ->assertSee('O campo banco é obrigatório.')
            ->assertNoRedirect();
        $this->assertSame('0040', $processo->refresh()->codigo_banco);
    }

    public function test_legacy_edit_preserves_export_fields_without_classifying_process(): void
    {
        [$user, $empresa] = $this->createTenant('EDIT-EXPORT');
        [$estancia, $tipo] = $this->createLookupData();
        $processo = $this->createProcesso($empresa, $user,
            $this->createCustomer($empresa, $user, 'EDIT-EXPORT'),
            $this->createExportador($empresa, $user, 'EDIT-EXPORT'), $estancia, $tipo);
        $id = DB::table('regiao_aduaneiras')->insertGetId([
            'codigo' => '1', 'abrev' => 'EX', 'descricao' => 'Exportacao edicao',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->grantTenantPermissions($user, ['processos.update']);
        $this->signInTenant($user);

        Livewire::test(ProcessoEdit::class, ['processo' => $processo])
            ->set('TipoProcesso', (string) $id)
            ->assertDontSee('Dados de exportação CRUD')
            ->set('codigo_banco', '0040')
            ->set('rsm_num', 'EDIT-RSM')
            ->call('update')->assertHasNoErrors()
            ->assertRedirect(route('processos.show', $processo));
        $this->assertSame('EDIT-RSM', $processo->refresh()->rsm_num);
    }

    public function test_create_saves_valid_data_without_optional_vinheta(): void
    {
        [$user, $empresa] = $this->createTenant('SAVE');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'SAVE');
        $exportador = $this->createExportador($empresa, $user, 'SAVE');
        $this->grantTenantPermissions($user, ['processos.create', 'processos.view']);
        $this->signInTenant($user);

        $component = Livewire::test(ProcessoCreate::class);
        if (getenv('PROCESSO_CREATE_BROWSER_FIXTURE')) {
            @mkdir(storage_path('app/processo-create-qa'), 0777, true);
            file_put_contents(storage_path('app/processo-create-qa/form.html'), $component->html());
        }
        $component->set('customer_id', $customer->id)
            ->set('exportador_id', $exportador->id)
            ->set('estancia_id', $estanciaId)
            ->set('TipoProcesso', (string) $tipoProcessoId)
            ->set('forma_pagamento', 'RD')
            ->set('codigo_banco', '0040')
            ->set('fob_total', 200)
            ->set('frete', 25)
            ->set('seguro', 5)
            ->set('Cambio', 2)
            ->call('save');

        $this->assertEmpty($component->instance()->getErrorBag()->all(), implode('\n', $component->instance()->getErrorBag()->all()));

        $processo = DB::table('processos')->where('empresa_id', $empresa->id)->sole();
        $this->assertSame($customer->id, $processo->customer_id);
        $this->assertSame('geral', $processo->natureza_operacao);
        $this->assertSame(230.0, (float) $processo->cif);
        $this->assertSame(460.0, (float) $processo->ValorAduaneiro);
        $this->assertNotEmpty($processo->NrProcesso);
    }

    public function test_create_displays_validation_errors_for_fields_without_inline_feedback(): void
    {
        [$user] = $this->createTenant('ERROR');
        $this->grantTenantPermissions($user, ['processos.create']);
        $this->signInTenant($user);

        Livewire::test(ProcessoCreate::class)
            ->set('NrDAR', 'abc')
            ->call('save')
            ->assertHasErrors(['NrDAR' => 'integer', 'forma_pagamento' => 'required', 'codigo_banco' => 'required'])
            ->assertSee('O campo número DAR deve ser um número inteiro.');
    }

    public function test_create_displays_persistence_failure_and_keeps_form_data(): void
    {
        [$user, $empresa] = $this->createTenant('FAIL');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'FAIL');
        $exportador = $this->createExportador($empresa, $user, 'FAIL');
        $this->grantTenantPermissions($user, ['processos.create']);
        $this->signInTenant($user);

        Livewire::test(ProcessoCreate::class)
            ->set('customer_id', $customer->id)
            ->set('exportador_id', $exportador->id)
            ->set('estancia_id', $estanciaId)
            ->set('TipoProcesso', (string) $tipoProcessoId)
            ->set('vinheta', 'FAIL-001')
            ->set('forma_pagamento', 'RD')
            ->set('codigo_banco', '0040')
            ->set('Estado', 'Finalizado')
            ->call('save')
            ->assertSee('Crie o processo aberto e utilize o comando de finalização.')
            ->assertSet('vinheta', 'FAIL-001');

        $this->assertSame(0, DB::table('processos')->where('empresa_id', $empresa->id)->count());
    }

    public function test_general_export_does_not_show_petroleum_fields(): void
    {
        [$user] = $this->createTenant('EXPORT');
        $this->grantTenantPermissions($user, ['processos.create']);
        $this->signInTenant($user);
        $component = Livewire::test(ProcessoCreate::class, ['exportacao' => true]);
        $component->assertDontSeeHtml('wire:model="rsm_num"');
        $tipo = $component->get('tipoProcessoOptions')->firstWhere('id', $component->get('TipoProcesso'));
        $this->assertSame('EX', $tipo->abrev);
        $this->assertSame('1', (string) $tipo->codigo);
    }
    public function test_create_saves_export_fields_with_central_validation(): void
    {
        [$user, $empresa] = $this->createTenant('EXPORT-SAVE');
        [$estanciaId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'EXPORT-SAVE');
        $exportador = $this->createExportador($empresa, $user, 'EXPORT-SAVE');
        $tipoId = DB::table('regiao_aduaneiras')->insertGetId([
            'codigo' => '1', 'abrev' => 'EX', 'descricao' => 'Exportacao gravacao',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->grantTenantPermissions($user, ['processos.create', 'processos.view']);
        $this->signInTenant($user);

        $component = Livewire::test(ExportacaoCrudeCreate::class)
            ->set('customer_id', $customer->id)
            ->set('exportador_id', $exportador->id)
            ->set('estancia_id', $estanciaId)
            ->set('TipoProcesso', (string) $tipoId)
            ->set('forma_pagamento', 'RD')
            ->set('codigo_banco', '0040')
            ->set('quantidade_barris', '10')
            ->set('num_deslocacoes', '2')
            ->set('rsm_num', 'RSM-SAVE')
            ->call('save');

        $component->assertHasNoErrors();
        $processo = DB::table('processos')->where('empresa_id', $empresa->id)->sole();
        $this->assertSame('RSM-SAVE', $processo->rsm_num);
        $this->assertSame('2', (string) $processo->num_deslocacoes);
        $this->assertSame(10, (int) $processo->quantidade_barris);
    }

    public function test_create_rejects_cross_tenant_customer_and_exportador_ids(): void
    {
        [$tenantAUser, $tenantAEmpresa] = $this->createTenant('A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('B');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();

        $customerB = $this->createCustomer($tenantBEmpresa, $tenantBUser, 'B');
        $exportadorB = $this->createExportador($tenantBEmpresa, $tenantBUser, 'B');

        $this->grantTenantPermissions($tenantAUser, ['processos.create', 'processos.view']);
        $this->signInTenant($tenantAUser);

        Livewire::test(ProcessoCreate::class)
            ->set('customer_id', $customerB->id)
            ->set('exportador_id', $exportadorB->id)
            ->set('estancia_id', $estanciaId)
            ->set('vinheta', 'V-001')
            ->set('TipoProcesso', (string) $tipoProcessoId)
            ->call('save')
            ->assertHasErrors(['customer_id', 'exportador_id']);

        $this->assertSame(0, DB::table('processos')->where('empresa_id', $tenantAEmpresa->id)->count());
    }

    public function test_edit_hydrates_aliases_and_recalculates_values(): void
    {
        [$tenantUser, $tenantEmpresa] = $this->createTenant('A2');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($tenantEmpresa, $tenantUser, 'A2');
        $exportador = $this->createExportador($tenantEmpresa, $tenantUser, 'A2');
        $processo = $this->createProcesso($tenantEmpresa, $tenantUser, $customer, $exportador, $estanciaId, $tipoProcessoId, [
            'N_Dar' => 77,
            'MarcaFiscal' => 'MF-77',
            'Cambio' => 2,
        ]);

        $this->grantTenantPermissions($tenantUser, ['processos.view', 'processos.update']);
        $this->signInTenant($tenantUser);

        $component = Livewire::test(ProcessoEdit::class, ['processo' => $processo]);

        if (Schema::hasColumn('processos', 'N_Dar')) {
            $component->assertSet('NrDAR', 77);
        }

        if (Schema::hasColumn('processos', 'MarcaFiscal')) {
            $component->assertSet('NrMarcaFiscal', 'MF-77');
        }

        $component
            ->set('fob_total', 200)
            ->set('frete', 25)
            ->set('seguro', 5)
            ->set('Cambio', 2)
            ->call('update')
            ->assertHasNoErrors();

        $processo->refresh();
        if (Schema::hasColumn('processos', 'cif')) {
            $this->assertSame(230.0, (float) $processo->cif);
        }

        if (Schema::hasColumn('processos', 'ValorAduaneiro')) {
            $this->assertSame(460.0, (float) $processo->ValorAduaneiro);
        }
    }

    public function test_show_rejects_cross_tenant_process(): void
    {
        [$tenantAUser] = $this->createTenant('A3');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('B3');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customerB = $this->createCustomer($tenantBEmpresa, $tenantBUser, 'B3');
        $exportadorB = $this->createExportador($tenantBEmpresa, $tenantBUser, 'B3');
        $processoB = $this->createProcesso($tenantBEmpresa, $tenantBUser, $customerB, $exportadorB, $estanciaId, $tipoProcessoId);

        $this->grantTenantPermissions($tenantAUser, ['processos.create', 'processos.view']);
        $this->signInTenant($tenantAUser);

        Livewire::test(ProcessoShow::class, ['processo' => $processoB])
            ->assertNotFound();
    }
}
