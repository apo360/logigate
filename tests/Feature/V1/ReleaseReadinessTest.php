<?php
namespace Tests\Feature\V1;

use Tests\Support\IsolatedDatabaseTestCase;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\Feature\Licenciamento\LicenciamentoTestSupport;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class ReleaseReadinessTest extends IsolatedDatabaseTestCase
{
    use ProcessoTestFixtures, LicenciamentoTestSupport;
    private function scenario(): array
    {
        [$actor, $company] = $this->createTenant('V1NEW');
        $this->actingAs($actor);
        TenantContext::setEmpresa($actor, $company);
        $permissions = ['customers.create','exportadores.create','exportadores.view','exportadores.update','exportadores.delete','licenciamentos.create','licenciamentos.update','processos.create'];
        foreach ($permissions as $permission) { Permission::findOrCreate($permission, 'web'); }
        $actor->syncPermissions($permissions);
        return [$actor, $company];
    }
    public function test_exportador_modal_creation_detail_edit_and_removal(): void
    {
        [$actor, $company] = $this->scenario();
        $country = DB::table('paises')->value('id');
        $component = \Livewire\Livewire::test(\App\Livewire\Forms\ExportadorQuickForm::class)->call('open')->assertSee('Novo exportador');
        $component->set('Exportador', 'Exportador V1')->set('ExportadorTaxID', '9123456789')->set('Pais', $country)->call('save')->assertHasNoErrors()->assertDispatched('exportadorCriado');
        $exporter = $company->exportadors()->where('ExportadorTaxID', '9123456789')->sole();
        \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag());
        $data = app(\App\Domains\Exportadores\Queries\ExportadorDetailQuery::class)->execute($exporter->id, $company);
        self::assertStringContainsString('Exportador V1', view('exportadors.show', $data)->render());
        $form = \App\Domains\Exportadores\Data\ExportadorFormData::fromArray(['Exportador' => 'Alterado V1', 'Pais' => $country]);
        app(\App\Domains\Exportadores\Actions\UpdateExportadorAction::class)->execute($exporter, $company, $form, 'global');
        self::assertSame('Alterado V1', $exporter->refresh()->Exportador);
        self::assertStringContainsString('Associação à empresa', view('exportadors.edit', ['exportador' => $exporter, 'association' => $exporter->empresas()->whereKey($company->id)->sole()->pivot, 'paises' => \App\Models\Pais::all()])->render());
        app(\App\Domains\Exportadores\Actions\DeleteExportadorAction::class)->execute($exporter, $company, $actor);
        self::assertFalse($company->exportadors()->whereKey($exporter->id)->exists());
        self::assertTrue(DB::table('exportadors')->where('id', $exporter->id)->exists());
    }
    public function test_exportador_counter_is_company_scoped(): void
    {
        [$actor, $company] = $this->scenario();
        $license = $this->createLicenciamentoFor($company, $actor, 'V1COUNTER');
        $repository = app(\App\Domains\Exportadores\Repositories\ExportadorRepositoryInterface::class);
        self::assertSame(1, $repository->statsForEmpresa($company)->com_licenciamentos);
        self::assertNotNull($repository->paginateForEmpresa($company)->first()->pivot);
        \Livewire\Livewire::test(\App\Livewire\Tables\ExportadorTable::class)->assertSee('Com licenciamentos')->assertSee($license->exportador->Exportador);
    }
    public function test_customer_import_accepts_valid_line_and_rejects_invalid_line(): void
    {
        [$actor, $company] = $this->scenario();
        $import = new \App\Imports\CustomersImport($company, $actor);
        $import->collection(collect([
            ['customertaxid' => '9234567890', 'companyname' => 'Cliente V1', 'customertype' => 'Empresa', 'telephone' => '923456789', 'tipocliente' => 'importador'],
            ['customertaxid' => '1', 'companyname' => 'Erro', 'customertype' => 'Empresa', 'telephone' => '1', 'tipocliente' => 'importador'],
        ]));
        self::assertSame(1, $import->result->toArray()['accepted'], json_encode($import->result->toArray()));
        self::assertSame(1, $import->result->toArray()['rejected']);
        self::assertSame(1, $company->customers()->where('CustomerTaxID','9234567890')->count());
    }
    public function test_process_import_rejects_foreign_references_and_finalization_columns(): void
    {
        [$actor, $company] = $this->scenario();
        [$otherActor, $otherCompany] = $this->createTenant('V1OTHER');
        [$estancia, $tipo] = $this->createLookupData();
        $customer = $this->createCustomer($otherCompany, $otherActor, 'FOREIGN');
        $exporter = $this->createExportador($otherCompany, $otherActor, 'FOREIGN');
        $import = new \App\Imports\ProcessosImport($company, $actor);
        $import->collection(collect([
            ['customer_id' => $customer->id, 'exportador_id' => $exporter->id, 'estancia_id' => $estancia, 'tipoprocesso' => (string)$tipo],
            ['contadespacho' => 'CCD-001/2026'],
        ]));
        self::assertSame(0, $import->result->toArray()['accepted']);
        self::assertSame(2, $import->result->toArray()['rejected']);
    }
    public function test_license_import_recalculates_cif_and_rejects_bad_reference(): void
    {
        [$actor, $company] = $this->scenario();
        $license = $this->createLicenciamentoFor($company, $actor, 'V1IMPORT');
        $row = ['cliente_id' => $license->cliente_id, 'exportador_id' => $license->exportador_id, 'estancia_id' => $license->estancia_id, 'referencia_cliente' => 'REF-IMPORT', 'factura_proforma' => 'FP-IMPORT', 'descricao' => 'Import V1', 'moeda' => 'USD', 'tipo_declaracao' => '11', 'tipo_transporte' => '3', 'metodo_avaliacao' => 'GATT', 'codigo_volume' => 'B', 'qntd_volume' => 1, 'forma_pagamento' => 'RD', 'fob_total' => 100, 'frete' => 10, 'seguro' => 5, 'cif' => 999, 'peso_bruto' => 2, 'porto_entrada' => 'LAD', 'codigo_banco' => '0040'];
        $import = new \App\Imports\LicenciamentosImport($company, $actor);
        $import->collection(collect([$row, array_replace($row, ['exportador_id' => 999999999])]));
        self::assertSame(1, $import->result->toArray()['accepted'], json_encode($import->result->toArray()));
        self::assertSame(1, $import->result->toArray()['rejected']);
        self::assertSame(115.0, (float)DB::table('licenciamentos')->where('id',$import->result->rows[0]['id'])->value('cif'));
    }
    public function test_sequence_uses_emission_year_and_includes_legacy_maximum(): void
    {
        [$actor, $company] = $this->scenario();
        $license = $this->createLicenciamentoFor($company, $actor, 'V1SERIES');
        [$estancia, $tipo] = $this->createLookupData();
        $process = $this->createProcesso($company, $actor, $license->cliente, $license->exportador, $estancia, $tipo, ['ContaDespacho' => 'CCD-017/2026', 'created_at' => '2020-01-01']);
        self::assertSame(18, app(\App\Domains\Processo\Services\OperationalSequence::class)->reserve($company->id, 'conta_despacho', 2026));
        self::assertSame(19, app(\App\Domains\Processo\Services\OperationalSequence::class)->reserve($company->id, 'conta_despacho', 2026));
    }
    public function test_customer_worker_runs_without_http_context_and_restores_it(): void
    {
        [$actor, $company] = $this->scenario();
        $csv = "CustomerTaxID,CompanyName,CustomerType,Telephone,TipoCliente\n9345678901,Cliente fila,Empresa,934567890,importador\n";
        \Illuminate\Support\Facades\Storage::disk('local')->put('clientes-job.csv', $csv);
        $batch = \App\Models\Migracao::create(['type'=>'clientes','file_path'=>'clientes-job.csv','status'=>'pending','empresa_id'=>$company->id,'actor_id'=>$actor->id]);
        \Illuminate\Support\Facades\Auth::guard('web')->forgetUser(); session()->forget('empresa_id');
        $job = new \App\Jobs\ImportCustomers(\Illuminate\Support\Facades\Storage::disk('local')->path('clientes-job.csv'),$batch->id,$company->id,$actor->id);
        $job->handle(app(\App\Application\Importacao\ImportExecutionContext::class));
        self::assertNull(\Illuminate\Support\Facades\Auth::user());
        self::assertNull(session('empresa_id'));
        self::assertSame('completed',$batch->refresh()->status);
        self::assertSame(1,$batch->result['accepted']);
        $job->handle(app(\App\Application\Importacao\ImportExecutionContext::class));
        self::assertSame(1,DB::table('customers')->where('CustomerTaxID','9345678901')->count());
    }
    public function test_revoked_permission_prevents_queued_customer_import(): void
    {
        [$actor, $company] = $this->scenario();
        $batch=\App\Models\Migracao::create(['type'=>'clientes','file_path'=>'never-read.csv','status'=>'pending','empresa_id'=>$company->id,'actor_id'=>$actor->id]);
        $actor->revokePermissionTo('customers.create');
        $job=new \App\Jobs\ImportCustomers('never-read.csv',$batch->id,$company->id,$actor->id);
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $job->handle(app(\App\Application\Importacao\ImportExecutionContext::class));
    }

}
