<?php

namespace Tests\Feature\Processo;

use App\Application\Processo\Actions\AtualizarProcessoAction;
use App\Application\Processo\Actions\CriarProcessoAction;
use App\Application\Processo\Actions\FinalizarProcessoAction;
use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Application\Processo\DTOs\CriarProcessoDTO;
use App\Application\Mercadoria\Services\MercadoriaTenantAccessService;
use App\Models\Mercadoria;
use App\Models\Processo;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Spatie\Permission\Models\Permission;
use Tests\Support\IsolatedDatabaseTestCase;
use App\Livewire\Processo\ProcessoEdit;
use Livewire\Livewire;

class ProcessoLifecycleAuditTest extends IsolatedDatabaseTestCase
{
    use ProcessoTestFixtures;
    use \Tests\Feature\Licenciamento\LicenciamentoTestSupport;

    private function scenario(array $permissions = ['processos.update']): Processo
    {
        [$user, $empresa] = $this->createTenant('LIFECYCLE');
        [$estancia, $tipo] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'LIFECYCLE');
        $exportador = $this->createExportador($empresa, $user, 'LIFECYCLE');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estancia, $tipo);
        $this->actingAs($user);
        TenantContext::setEmpresa($user, $empresa);
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user->syncPermissions($permissions);
        return $processo;
    }

    private function rejected(callable $operation, string $exception = InvalidArgumentException::class): void
    {
        try {
            $operation();
            self::fail('Expected operation to be rejected.');
        } catch (InvalidArgumentException|AuthorizationException $error) {
            self::assertInstanceOf($exception, $error);
        }
    }

    public function test_update_cannot_finalize_or_write_finalization_fields(): void
    {
        $processo = $this->scenario();
        foreach ([['Estado' => 'Finalizado'], ['ContaDespacho' => 'CCD-999/2026'], ['DataFecho' => '2026-10-01']] as $payload) {
            $this->rejected(fn () => app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray(['id' => $processo->id] + $payload)));
        }
        $this->rejected(fn () => app(FinalizarProcessoAction::class)->execute($processo->id), AuthorizationException::class);
        self::assertSame('Aberto', $processo->refresh()->Estado);
        self::assertNull($processo->ContaDespacho);
        self::assertNull($processo->DataFecho);
    }

    public function test_create_cannot_bypass_finalization(): void
    {
        $processo = $this->scenario(['processos.create']);
        $payload = $processo->getAttributes();
        $payload['Estado'] = 'Finalizado';
        $this->rejected(fn () => app(CriarProcessoAction::class)->execute(CriarProcessoDTO::fromArray($payload)));
    }

    public function test_livewire_cannot_finalize_with_only_update_permission(): void
    {
        $processo = $this->scenario();
        $component = Livewire::test(ProcessoEdit::class, ['processo' => $processo]);
        foreach ($component->get('EstadoOptions') as $option) {
            self::assertNotSame('Finalizado', $option->value);
        }
        $component->set('codigo_banco', '0040')->set('Estado', 'Finalizado')->call('update')->assertHasErrors('processo')->assertNoRedirect();
        self::assertSame('Aberto', $processo->refresh()->Estado);
    }

    public function test_shared_merchandise_cannot_bypass_terminal_lock_via_licenciamento(): void
    {
        $processo = $this->scenario(['mercadorias.update', 'mercadorias.delete']);
        $user = auth()->user();
        $licenciamento = $this->createLicenciamentoFor(TenantContext::empresa(), $user, 'LOCK-SHARED');
        $mercadoria = Mercadoria::query()->create(['Fk_Importacao' => $processo->id, 'licenciamento_id' => $licenciamento->id, 'Descricao' => 'Partilhada']);
        DB::table('processos')->where('id', $processo->id)->update(['Estado' => 'Finalizado']);
        foreach (['mercadorias.update', 'mercadorias.delete'] as $permission) {
            $this->rejected(fn () => app(MercadoriaTenantAccessService::class)->authorizeMercadoria($user, $mercadoria, 'licenciamento', $licenciamento->id, $permission));
        }
        self::assertSame($processo->id, (int) $mercadoria->refresh()->Fk_Importacao);
    }

    public function test_livewire_persists_optional_fields_and_clears_them_without_an_unapplied_migration(): void
    {
        $processo = $this->scenario();
        $component = Livewire::test(ProcessoEdit::class, ['processo' => $processo])
            ->set('codigo_banco', '0040')->set('vinheta', 'V-LIVEWIRE')
            ->set('certificado_origem', 'CO-LIVEWIRE')->set('guia_exportacao', 'GE-LIVEWIRE')
            ->call('update');
        self::assertSame([], $component->instance()->getErrorBag()->all());
        $component->assertRedirect(route('processos.show', $processo));
        self::assertSame('V-LIVEWIRE', $processo->refresh()->vinheta);
        self::assertSame('CO-LIVEWIRE', $processo->certificado_origem);
        self::assertSame('GE-LIVEWIRE', $processo->guia_exportacao);
        Livewire::test(ProcessoEdit::class, ['processo' => $processo])
            ->set('certificado_origem', null)->set('guia_exportacao', '')
            ->call('update')->assertHasNoErrors()->assertRedirect(route('processos.show', $processo));
        self::assertNull($processo->refresh()->certificado_origem);
        self::assertNull($processo->guia_exportacao);
    }

    public function test_update_persists_export_fields_and_can_clear_optional_fields(): void
    {
        $processo = $this->scenario();
        $fields = ['vinheta' => 'AUDIT-001', 'quantidade_barris' => 0,
            'data_carregamento' => '2026-10-01', 'valor_barril_usd' => 12.5, 'num_deslocacoes' => '2',
            'rsm_num' => 'RSM-1', 'certificado_origem' => 'CO-1', 'guia_exportacao' => 'GE-1'];
        $updated = app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray(['id' => $processo->id] + $fields));
        foreach ($fields as $field => $value) {
            self::assertEquals($value, $updated->getAttribute($field), $field);
        }
        $updated = app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray(['id' => $processo->id, 'certificado_origem' => null, 'guia_exportacao' => null]));
        self::assertNull($updated->certificado_origem);
        self::assertNull($updated->guia_exportacao);
        self::assertSame('RSM-1', $updated->rsm_num);
        if (Schema::hasColumn('processos', 'DataPartida')) {
            $updated = app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray(['id' => $processo->id, 'DataPartida' => '2026-10-01']));
            self::assertSame('2026-10-01', $updated->DataPartida);
        } else {
            $this->rejected(fn () => app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray(['id' => $processo->id, 'DataPartida' => '2026-10-01'])));
        }
    }

    public function test_terminal_process_allows_only_notes_and_blocks_merchandise_writes(): void
    {
        $processo = $this->scenario(['processos.update', 'mercadorias.create', 'mercadorias.update', 'mercadorias.delete']);
        foreach (['Finalizado', 'Cancelado'] as $estado) {
            DB::table('processos')->where('id', $processo->id)->update(['Estado' => $estado]);
            app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray(['id' => $processo->id, 'observacoes' => 'Nota ' . $estado]));
            self::assertSame('Nota ' . $estado, $processo->refresh()->observacoes);
            $this->rejected(fn () => app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray(['id' => $processo->id, 'fob_total' => 999])));
            $this->rejected(fn () => app(AtualizarProcessoAction::class)->execute(AtualizarProcessoDTO::fromArray(['id' => $processo->id, 'Estado' => 'Aberto'])));
            foreach (['mercadorias.create', 'mercadorias.update', 'mercadorias.delete'] as $permission) {
                $this->rejected(fn () => app(MercadoriaTenantAccessService::class)->authorizeContext(auth()->user(), 'processo', $processo->id, $permission));
            }
        }
    }

    public function test_finalization_checks_requirements_and_generates_required_fields(): void
    {
        $processo = $this->scenario(['processos.finalize']);
        $this->rejected(fn () => app(FinalizarProcessoAction::class)->execute($processo->id));
        DB::table('processos')->where('id', $processo->id)->update(['NrDU' => 'DU-1', 'BLC_Porte' => 'BL-1']);
        if (Schema::hasColumn('processos', 'cambio_confirmado')) {
            DB::table('processos')->where('id', $processo->id)->update(['cambio_confirmado' => true, 'cambio_origem' => 'Documento de teste', 'cambio_data' => now()->toDateString()]);
        }
        Mercadoria::query()->create(['Fk_Importacao' => $processo->id, 'Descricao' => 'Teste', 'Quantidade' => 1, 'Peso' => 1, 'preco_unitario' => 100, 'preco_total' => 100, 'codigo_aduaneiro' => '01012100']);
        DB::table('emolumento_tarifas')->insert(['processo_id' => $processo->id, 'honorario' => 10, 'created_at' => now(), 'updated_at' => now()]);
        if (! Schema::hasColumn('processos', 'cambio_confirmado')) {
            $this->rejected(fn () => app(FinalizarProcessoAction::class)->execute($processo->id));
            self::assertNull($processo->refresh()->ContaDespacho);
            self::assertNull($processo->DataFecho);
            return;
        }
        $finalized = app(FinalizarProcessoAction::class)->execute($processo->id);
        self::assertSame('Finalizado', $finalized->Estado);
        self::assertMatchesRegularExpression('/^CCD-\d+\/\d{4}$/', $finalized->ContaDespacho);
        self::assertSame(now()->toDateString(), substr((string) $finalized->DataFecho, 0, 10));
        $this->rejected(fn () => app(FinalizarProcessoAction::class)->execute($processo->id));
        self::assertSame($finalized->ContaDespacho, $processo->refresh()->ContaDespacho);
    }
}
