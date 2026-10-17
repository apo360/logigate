<?php

namespace Tests\Feature\Processo;

use App\Application\Processo\Services\ProcessoCustoService;
use App\Livewire\PautaAduaneira\SimuladorProcesso;
use App\Livewire\Processo\ControloCustos;
use App\Livewire\Processo\ProcessoShow;
use App\Models\DocumentoArquivo;
use App\Models\ExternalInvoice;
use App\Models\Mercadoria;
use App\Models\PautaAduaneira;
use App\Models\Processo;
use App\Models\ProcessoCustoAlerta;
use App\Models\ProcessoCustoEvento;
use App\Models\ProcessoCustoRegisto;
use App\Models\ProcessoCustoSimulacao;
use App\Support\CompanyRbac;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\IsolatedDatabaseTestCase;

class ControloCustosTest extends IsolatedDatabaseTestCase
{
    use ProcessoTestFixtures;

    private function scenario(string $suffix = 'CUSTO'): array
    {
        [$user, $empresa] = $this->createTenant($suffix);
        [$estancia, $tipo] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, $suffix);
        $exportador = $this->createExportador($empresa, $user, $suffix);
        $this->grantTenantPermissions($user, ['processos.view', 'processos.update', 'processos.simulate', 'documents.view', 'documents.download']);
        $this->signInTenant($user);
        $p = $this->createProcesso($empresa, $user, $customer, $exportador, $estancia, $tipo, ['Moeda' => 'USD', 'natureza_operacao' => 'geral']);
        $pauta = PautaAduaneira::create(['codigo' => '9999.'.random_int(1000, 9999), 'descricao' => 'Pauta teste', 'rg' => 10, 'sadc' => 0, 'ua' => 0, 'iva' => 14, 'ieq' => 0]);
        Mercadoria::create(['Fk_Importacao' => $p->id, 'Descricao' => 'Carga', 'preco_total' => 100, 'pauta_aduaneira_id' => $pauta->id, 'codigo_aduaneiro' => $pauta->codigo]);

        return [$p, $user, $empresa, $pauta];
    }

    private function document(Processo $p): DocumentoArquivo
    {
        return DocumentoArquivo::create(['uuid' => (string) Str::uuid(), 'empresa_id' => $p->empresa_id, 'processo_id' => $p->id, 'contexto' => 'processo', 'categoria' => 'comprovativos', 'storage_disk' => 'local', 'storage_key' => 'teste/'.Str::uuid().'.pdf', 'nome_original' => 'comprovativo.pdf']);
    }

    private function entry(string $phase, $value, ?DocumentoArquivo $doc = null, array $extra = []): array
    {
        return array_replace(['fase' => $phase, 'escopo' => 'tributos', 'valor' => $value, 'data' => today()->toDateString(), 'referencia' => (string) Str::uuid(), 'descricao' => 'Valor documentado de teste', 'documento_id' => $doc?->id], $extra);
    }

    private function params(): array
    {
        return ['regimeTaxa' => 'rg', 'incluirIva' => true, 'incluirIeq' => true, 'regimesPorMercadoria' => []];
    }

    private function rejects(callable $fn, string $field): void
    {
        try {
            $fn();
            $this->fail('Expected validation rejection: '.$field);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors());
        }
    }

    public function test_versions_recalculate_server_side_preserve_inputs_and_detect_changed_rates(): void
    {
        [$p, , , $pauta] = $this->scenario();
        $s = app(ProcessoCustoService::class);
        $v = $s->guardarSimulacao($p->id, $this->params());
        $this->assertTrue($v->completa);
        $this->assertEquals(29.21, $v->resultado['totais']['total_impostos']);
        $this->assertEquals(29.21, $s->relatorio($p->id)['totals']['estimado']);
        $this->assertSame($v->id, $s->guardarSimulacao($p->id, $this->params())->id);
        $pauta->update(['rg' => 20]);
        $this->assertNull($s->relatorio($p->id)['totals']['estimado']);
        $v2 = $s->guardarSimulacao($p->id, $this->params());
        $this->assertSame(2, $v2->versao);
        $this->assertEquals(10, $v->fresh()->entradas['mercadorias'][0]['pauta']['rg']);
        $this->assertEquals(42.32, $v2->resultado['totais']['total_impostos']);
    }

    public function test_unknown_is_not_zero_and_latest_declaration_is_a_revision_not_an_addition(): void
    {
        [$p] = $this->scenario();
        $s = app(ProcessoCustoService::class);
        $this->assertNull($s->relatorio($p->id)['totals']['tributos']['declarado']);
        $s->registar($p->id, $this->entry('declarado', 100));
        $this->assertNull($s->relatorio($p->id)['totals']['tributos']['declarado']);
        $doc = $this->document($p);
        $zero = $s->registar($p->id, $this->entry('declarado', 0, $doc));
        $this->assertSame(0.0, $s->relatorio($p->id)['totals']['tributos']['declarado']);
        $s->registar($p->id, $this->entry('declarado', 50, $doc));
        $this->assertSame(50.0, $s->relatorio($p->id)['totals']['tributos']['declarado']);
        $doc->delete();
        $this->assertNull($s->relatorio($p->id)['totals']['tributos']['declarado']);
        $this->assertNotNull($zero->fresh());
    }

    public function test_invoice_allocations_are_capped_and_cancellation_reopens_recovery_alert(): void
    {
        [$p] = $this->scenario();
        $s = app(ProcessoCustoService::class);
        $doc = $this->document($p);
        $paid = $s->registar($p->id, $this->entry('pago', 100, $doc));
        $invoice = ExternalInvoice::create(['empresa_id' => $p->empresa_id, 'customer_id' => $p->customer_id, 'processo_id' => $p->id, 'currency' => 'AOA', 'status' => 'issued', 'document_type' => 'FT', 'gross_total' => 100]);
        $data = $this->entry('faturado', 100, null, ['external_invoice_id' => $invoice->id, 'origem_id' => $paid->id]);
        $billed = $s->registar($p->id, $data);
        $this->assertArrayNotHasKey('recuperacao_'.$paid->id, $s->relatorio($p->id)['alerts']);
        $this->rejects(fn () => $s->registar($p->id, array_replace($data, ['referencia' => 'EXCESSO', 'valor' => 0.01])), 'valor');
        $this->rejects(fn () => $s->anular($p->id, $paid->id, 'Correccao documentada'), 'motivo');
        $invoice->update(['status' => 'cancelled']);
        $s->sincronizar($p->id);
        $this->assertNull($s->relatorio($p->id)['totals']['tributos']['faturado']);
        $this->assertArrayHasKey('recuperacao_'.$paid->id, $s->relatorio($p->id)['alerts']);
        $s->anular($p->id, $billed->id, 'Factura anulada no fornecedor');
        $s->anular($p->id, $paid->id, 'Pagamento registado em duplicado');
        $this->assertNotNull($paid->fresh()->anulado_at);
    }

    public function test_alert_assignment_explanation_audit_and_recurrence(): void
    {
        [$p, $user] = $this->scenario();
        $s = app(ProcessoCustoService::class);
        $paid = $s->registar($p->id, $this->entry('pago', 100));
        $key = 'evidencia_'.$paid->id;
        $s->tratar($p->id, $key, ['estado' => 'justificado', 'responsavel_id' => $user->id, 'prazo' => today()->toDateString(), 'justificacao' => 'A aguardar o comprovativo bancario']);
        $s->sincronizar($p->id);
        $this->assertSame('justificado', ProcessoCustoAlerta::where('processo_id', $p->id)->where('chave', $key)->sole()->estado);
        $doc = $this->document($p);
        // Simulate evidence restoration/removal by the document lifecycle; no ledger edit endpoint exists.
        $paid->update(['documento_id' => $doc->id]);
        $s->sincronizar($p->id);
        $this->assertFalse(ProcessoCustoAlerta::where('processo_id', $p->id)->where('chave', $key)->sole()->detectado);
        $doc->delete();
        $s->sincronizar($p->id);
        $this->assertSame('aberto', ProcessoCustoAlerta::where('processo_id', $p->id)->where('chave', $key)->sole()->estado);
        $this->assertTrue(ProcessoCustoEvento::where('processo_id', $p->id)->where('acao', 'alerta_tratado')->exists());
    }

    public function test_foreign_process_and_foreign_documents_cannot_be_used(): void
    {
        [$a, $actor] = $this->scenario('A');
        [$b] = $this->scenario('B');
        $doc = $this->document($b);
        $this->signInTenant($actor);
        $s = app(ProcessoCustoService::class);
        try {
            $s->registar($a->id, $this->entry('pago', 100, $doc));
            $this->fail('Foreign document accepted');
        } catch (ModelNotFoundException) {
            $this->assertSame(0, ProcessoCustoRegisto::where('processo_id', $a->id)->count());
        }
        try {
            $s->processo($b->id);
            $this->fail('Foreign process accepted');
        } catch (ModelNotFoundException) {
            $this->assertTrue(true);
        }
        $this->expectException(ModelNotFoundException::class);
        Livewire::test(SimuladorProcesso::class, ['processoId' => $b->id]);
    }

    public function test_read_only_user_cannot_record_or_change_company_criteria(): void
    {
        [$p, $user] = $this->scenario();
        CompanyRbac::within((int) $p->empresa_id, fn () => $user->syncPermissions(['processos.view']));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->signInTenant($user->fresh());
        Livewire::test(ControloCustos::class)->call('seleccionar', $p->id)->assertSee('Por confirmar')->assertDontSee('Gravar registo')->call('actualizar')->assertForbidden();
        Livewire::test(ControloCustos::class)->set('processoId', $p->id)->call('configurar')->assertForbidden();
    }

    public function test_incomplete_simulation_and_export_never_become_confirmed_import_estimates(): void
    {
        [$p, , , $pauta] = $this->scenario();
        $s = app(ProcessoCustoService::class);
        $pauta->update(['rg' => null]);
        $this->assertFalse($s->guardarSimulacao($p->id, $this->params())->completa);
        $this->assertNull($s->relatorio($p->id)['totals']['estimado']);
        $p->forceFill(['natureza_operacao' => 'exportacao_crude'])->save();
        $this->rejects(fn () => $s->guardarSimulacao($p->id, $this->params()), 'simulacao');
    }

    public function test_livewire_persists_records_filters_pending_queue_and_renders_page(): void
    {
        [$p] = $this->scenario();
        $component = Livewire::test(ControloCustos::class)->call('seleccionar', $p->id)
            ->set('registo', $this->entry('declarado', 123.45))->call('gravar')->assertHasNoErrors()->assertSee('Registo guardado');
        $this->assertSame('123.45', ProcessoCustoRegisto::where('processo_id', $p->id)->sole()->valor);
        $component->set('pendencia', 'aberto')->assertSee($p->NrProcesso);
        config(['navigation.route_modules' => []]);
        app(ProcessoCustoService::class)->guardarSimulacao($p->id, $this->params());
        $response = $this->get(route('processos.tarifa', ['processo' => $p->id]));
        $response->assertOk()->assertSee('Controlo de Custos e Tributos')->assertDontSee('Peixe');
        if (getenv('EXPORT_CUSTOS_QA') === '1') {
            $dir = storage_path('app/processo-custos-qa');
            if (! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($dir.'/controlo.html', $response->getContent());
            file_put_contents($dir.'/simulador.html', $this->get(route('processos.simulador-pauta', $p))->assertOk()->getContent());
        }
    }

    public function test_comparables_use_documented_normalized_values_and_exclude_other_profiles(): void
    {
        [$p] = $this->scenario();
        $s = app(ProcessoCustoService::class);
        $s->guardarConfiguracao($p->id, ['limiar_percentual' => 10, 'limiar_absoluto' => 1, 'amostra_minima' => 3, 'periodo_dias' => 180]);
        $make = function (array $changes, float $value, bool $evidence = true) use ($p, $s) {
            $other = Processo::withoutEvents(function () use ($p, $changes) {
                $other = $p->replicate();
                $other->NrProcesso = 'HIST-'.Str::random(12);
                $other->fill($changes);
                $other->save();

                return $other;
            });
            $m = $p->mercadorias()->first()->replicate();
            $m->Fk_Importacao = $other->id;
            $m->save();
            $s->registar($other->id, $this->entry('declarado', $value, $evidence ? $this->document($other) : null));

            return $other;
        };
        foreach (range(1, 3) as $index) {
            $make(['ValorAduaneiro' => 115 * $index], 11.5 * $index);
        }
        $make(['natureza_operacao' => null], 9999);
        $make(['Moeda' => 'EUR'], 9999);
        $make([], 9999, false);
        $s->registar($p->id, $this->entry('declarado', 50, $this->document($p)));
        $comparison = $s->relatorio($p->id)['comparables'];
        $this->assertCount(3, $comparison['amostra']);
        $this->assertEquals(10, $comparison['mediana']);
        $this->assertTrue($comparison['desvio']);
        $this->assertArrayHasKey('historico', $s->relatorio($p->id)['alerts']);
        // A latest revision with another profile must not resurrect an older matching revision.
        $candidate = Processo::findOrFail($comparison['amostra'][0]['processo_id']);
        $candidate->forceFill(['Moeda' => 'EUR'])->save();
        $s->registar($candidate->id, $this->entry('declarado', 1, $this->document($candidate)));
        $this->assertNull($s->relatorio($p->id)['comparables']['mediana']);
    }

    public function test_duplicate_invalid_amounts_and_foreign_responsible_are_rejected(): void
    {
        [$p, $actor] = $this->scenario('VAL');
        [$foreign, $foreignUser] = $this->scenario('FOREIGN');
        $this->signInTenant($actor);
        $s = app(ProcessoCustoService::class);
        $data = $this->entry('pago', 20);
        $r = $s->registar($p->id, $data);
        $this->rejects(fn () => $s->registar($p->id, $data), 'referencia');
        foreach ([-1, 0.001, '1.234,56', 1000000000000] as $invalid) {
            $this->rejects(fn () => $s->registar($p->id, array_replace($data, ['valor' => $invalid, 'referencia' => 'INVALID'])), 'valor');
        }
        $this->rejects(fn () => $s->tratar($p->id, 'evidencia_'.$r->id, ['estado' => 'encaminhado', 'responsavel_id' => $foreignUser->id, 'justificacao' => 'Encaminhamento para revisao']), 'responsavel_id');
        $this->assertSame(1, ProcessoCustoRegisto::where('processo_id', $p->id)->count());
        $this->assertSame(0, ProcessoCustoEvento::where('processo_id', $foreign->id)->count());
    }

    public function test_simulator_version_button_recalculates_and_cannot_trust_public_result(): void
    {
        [$p] = $this->scenario();
        Livewire::test(SimuladorProcesso::class, ['processoId' => $p->id])
            ->set('resultado', ['totais' => ['total_impostos' => 999999], 'items' => [], 'alertas' => []])
            ->call('guardarVersao')->assertHasNoErrors()->assertSee('Versão 1 guardada');
        $this->assertEquals(29.21, ProcessoCustoSimulacao::where('processo_id', $p->id)->sole()->resultado['totais']['total_impostos']);
    }

    public function test_pending_document_does_not_confirm_values_and_scope_totals_remain_separate(): void
    {
        [$p] = $this->scenario();
        $s = app(ProcessoCustoService::class);
        $doc = $this->document($p);
        $s->registar($p->id, $this->entry('declarado', 100, $doc));
        $s->registar($p->id, $this->entry('pago', 50, $doc));
        $s->registar($p->id, $this->entry('pago', 700, $doc, ['escopo' => 'custos']));
        $this->assertSame(50.0, $s->relatorio($p->id)['totals']['tributos']['pago']);
        $this->assertSame(700.0, $s->relatorio($p->id)['totals']['custos']['pago']);
        $doc->update(['status' => 'pending']);
        $this->assertNull($s->relatorio($p->id)['totals']['tributos']['pago']);
        $this->assertCount(0, $s->documentos($p));
        $doc->update(['status' => 'activo']);
        $this->assertSame(50.0, $s->relatorio($p->id)['totals']['tributos']['pago']);
    }

    public function test_show_does_not_mount_import_simulator_for_crude_even_with_simulate_permission(): void
    {
        [$p] = $this->scenario();
        $p->forceFill(['natureza_operacao' => 'exportacao_crude'])->save();
        Livewire::test(ProcessoShow::class, ['processo' => $p])
            ->assertDontSeeHtml('wire:click="guardarVersao"')->assertSee($p->NrProcesso);
    }
}
