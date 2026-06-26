<?php

namespace Tests\Feature\Mercadoria;

use App\Application\Mercadoria\Actions\CriarMercadoriaAction;
use App\Application\Mercadoria\Actions\AtualizarMercadoriaAction;
use App\Application\Mercadoria\Actions\ExcluirMercadoriaAction;
use App\Application\Mercadoria\DTOs\MercadoriaData;
use App\Livewire\Mercadorias\CreateForm;
use App\Livewire\Mercadorias\Index;
use App\Models\Licenciamento;
use App\Models\Mercadoria;
use App\Models\PautaAduaneira;
use App\Models\Subcategoria;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Feature\Licenciamento\LicenciamentoTestSupport;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

class MercadoriaLicenciamentoLivewireTest extends TestCase
{
    use RefreshDatabase;
    use LicenciamentoTestSupport;
    use ProcessoTestFixtures;

    public function test_lists_only_mercadorias_from_current_licenciamento(): void
    {
        [$user, $empresa] = $this->createTenant('MER-LIST');
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'MER-LIST');
        $otherLicenciamento = $this->createLicenciamentoFor($empresa, $user, 'MER-LIST-OTHER');
        [, $pauta] = $this->createPautaFixture();

        $this->createMercadoria($licenciamento, $pauta, ['Descricao' => 'Mercadoria visivel']);
        $this->createMercadoria($otherLicenciamento, $pauta, ['Descricao' => 'Mercadoria escondida']);

        $this->actingAs($user);

        $component = Livewire::test(Index::class, ['context' => 'licenciamento', 'parentId' => $licenciamento->id]);
        $descricoes = collect($component->get('mercadorias'))->pluck('Descricao')->all();

        $this->assertContains('Mercadoria visivel', $descricoes);
        $this->assertNotContains('Mercadoria escondida', $descricoes);
    }

    public function test_modal_opens_without_hydration_error(): void
    {
        [$user, $empresa] = $this->createTenant('MER-MODAL');
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'MER-MODAL');
        $this->createPautaFixture();

        $this->actingAs($user);

        Livewire::test(CreateForm::class, ['context' => 'licenciamento', 'parentId' => $licenciamento->id])
            ->call('openModal')
            ->assertSet('open', true)
            ->assertSet('mode', 'create');
    }

    public function test_creates_mercadoria_for_licenciamento_and_updates_totals(): void
    {
        [$user, $empresa] = $this->createTenant('MER-CREATE');
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'MER-CREATE');
        $licenciamento->forceFill(['fob_total' => 0, 'peso_bruto' => 0, 'adicoes' => 0])->save();
        [$subcategoria, $pauta] = $this->createPautaFixture();

        $this->actingAs($user);

        Livewire::test(CreateForm::class, ['context' => 'licenciamento', 'parentId' => $licenciamento->id])
            ->set('form.subcategoria_id', $subcategoria->id)
            ->set('form.codigo_aduaneiro', $pauta->codigo)
            ->set('form.descricao', 'Mercadoria criada')
            ->set('form.quantidade', 3)
            ->set('form.peso', 12)
            ->set('form.unidade', 'Kg')
            ->set('form.preco_unitario', 20)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('mercadoriaCreated');

        $this->assertDatabaseHas('mercadorias', [
            'licenciamento_id' => $licenciamento->id,
            'Fk_Importacao' => null,
            'Descricao' => 'Mercadoria criada',
            'preco_total' => 60,
        ]);

        $licenciamento->refresh();
        $this->assertSame('60.00', (string) $licenciamento->fob_total);
        $this->assertSame('12.00', (string) $licenciamento->peso_bruto);
        $this->assertSame(1, (int) $licenciamento->adicoes);
    }

    public function test_updates_mercadoria_and_recalculates_total_and_parent_totals(): void
    {
        [$user, $empresa] = $this->createTenant('MER-UPDATE');
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'MER-UPDATE');
        $licenciamento->forceFill(['fob_total' => 0, 'peso_bruto' => 0, 'adicoes' => 0])->save();
        [$subcategoria, $pauta] = $this->createPautaFixture();
        $mercadoria = $this->createMercadoria($licenciamento, $pauta, [
            'subcategoria_id' => $subcategoria->id,
            'Quantidade' => 2,
            'Peso' => 5,
            'preco_unitario' => 15,
            'preco_total' => 30,
        ]);
        $licenciamento->forceFill(['fob_total' => 30, 'peso_bruto' => 5, 'adicoes' => 1])->save();

        $this->actingAs($user);

        Livewire::test(CreateForm::class, ['context' => 'licenciamento', 'parentId' => $licenciamento->id])
            ->call('openEditModal', $mercadoria->id)
            ->set('form.quantidade', 4)
            ->set('form.peso', 8)
            ->set('form.preco_unitario', 25)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('mercadoriaUpdated');

        $mercadoria->refresh();
        $this->assertSame('100.00', (string) $mercadoria->preco_total);

        $licenciamento->refresh();
        $this->assertSame('100.00', (string) $licenciamento->fob_total);
        $this->assertSame('8.00', (string) $licenciamento->peso_bruto);
    }

    public function test_deletes_mercadoria_and_updates_totals(): void
    {
        [$user, $empresa] = $this->createTenant('MER-DELETE');
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'MER-DELETE');
        $licenciamento->forceFill(['fob_total' => 0, 'peso_bruto' => 0, 'adicoes' => 0])->save();
        $pauta = $this->createPautaFixture()[1];
        $mercadoria = $this->createMercadoria($licenciamento, $pauta, [
            'Peso' => 4,
            'preco_total' => 40,
        ]);
        $licenciamento->forceFill(['fob_total' => 40, 'peso_bruto' => 4, 'adicoes' => 1])->save();

        $this->actingAs($user);

        Livewire::test(Index::class, ['context' => 'licenciamento', 'parentId' => $licenciamento->id])
            ->call('deleteItem', $mercadoria->id)
            ->assertDispatched('mercadoriaDeleted');

        $this->assertDatabaseMissing('mercadorias', ['id' => $mercadoria->id]);

        $licenciamento->refresh();
        $this->assertSame('0.00', (string) $licenciamento->fob_total);
        $this->assertSame('0.00', (string) $licenciamento->peso_bruto);
        $this->assertSame(0, (int) $licenciamento->adicoes);
    }

    public function test_groups_by_codigo_aduaneiro(): void
    {
        [$user, $empresa] = $this->createTenant('MER-GROUP');
        $licenciamento = $this->createLicenciamentoFor($empresa, $user, 'MER-GROUP');
        $licenciamento->forceFill(['adicoes' => 0])->save();
        $pauta = $this->createPautaFixture()[1];

        $this->createMercadoria($licenciamento, $pauta, ['Quantidade' => 1, 'Peso' => 2, 'preco_total' => 10]);
        $this->createMercadoria($licenciamento, $pauta, ['Quantidade' => 3, 'Peso' => 4, 'preco_total' => 30]);

        $this->assertDatabaseHas('mercadoria_agrupadas', [
            'licenciamento_id' => $licenciamento->id,
            'codigo_aduaneiro' => $pauta->codigo,
            'quantidade_total' => 4,
            'peso_total' => 6,
            'preco_total' => 40,
        ]);
    }

    public function test_blocks_cross_tenant_create_edit_and_delete(): void
    {
        [$tenantAUser] = $this->createTenant('MER-X-A');
        [$tenantBUser, $tenantBEmpresa] = $this->createTenant('MER-X-B');
        $licenciamentoB = $this->createLicenciamentoFor($tenantBEmpresa, $tenantBUser, 'MER-X-B');
        [$subcategoria, $pauta] = $this->createPautaFixture();
        $mercadoriaB = $this->createMercadoria($licenciamentoB, $pauta, ['subcategoria_id' => $subcategoria->id]);

        $this->actingAs($tenantAUser);

        try {
            app(CriarMercadoriaAction::class)->execute(MercadoriaData::fromLivewire([
                'subcategoria_id' => $subcategoria->id,
                'codigo_aduaneiro' => $pauta->codigo,
                'descricao' => 'Cross tenant',
                'quantidade' => 1,
                'peso' => 1,
                'unidade' => 'Kg',
                'preco_unitario' => 10,
            ], 'licenciamento', $licenciamentoB->id));
            $this->fail('Cross-tenant create should have been blocked.');
        } catch (AuthorizationException) {
            $this->assertTrue(true);
        }

        try {
            app(AtualizarMercadoriaAction::class)->execute(MercadoriaData::fromLivewire([
                'subcategoria_id' => $subcategoria->id,
                'codigo_aduaneiro' => $pauta->codigo,
                'descricao' => 'Cross tenant update',
                'quantidade' => 2,
                'peso' => 1,
                'unidade' => 'Kg',
                'preco_unitario' => 20,
            ], 'licenciamento', $licenciamentoB->id, $mercadoriaB->id));
            $this->fail('Cross-tenant edit should have been blocked.');
        } catch (AuthorizationException) {
            $this->assertTrue(true);
        }

        try {
            app(ExcluirMercadoriaAction::class)->execute($mercadoriaB->id, 'licenciamento', $licenciamentoB->id);
            $this->fail('Cross-tenant delete should have been blocked.');
        } catch (AuthorizationException) {
            $this->assertTrue(true);
        }
    }

    private function createPautaFixture(): array
    {
        $categoriaId = DB::table('categoria_aduaneira')->insertGetId([
            'nome' => 'Animais',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $subcategoria = Subcategoria::query()->create([
            'cod_pauta' => '02',
            'descricao' => 'Carnes',
            'categoria_id' => $categoriaId,
        ]);

        $pauta = PautaAduaneira::query()->create([
            'codigo' => '0203.11.00',
            'descricao' => 'Carnes suinas',
            'uq' => 'kg',
            'rg' => 0,
            'sadc' => 0,
            'ua' => 0,
            'requisitos' => '0',
            'observacao' => '0',
            'iva' => 0,
            'ieq' => 0,
        ]);

        return [$subcategoria, $pauta];
    }

    private function createMercadoria(Licenciamento $licenciamento, PautaAduaneira $pauta, array $overrides = []): Mercadoria
    {
        $mercadoria = Mercadoria::query()->create(array_merge([
            'licenciamento_id' => $licenciamento->id,
            'Fk_Importacao' => null,
            'codigo_aduaneiro' => $pauta->codigo,
            'Descricao' => 'Mercadoria teste',
            'Quantidade' => 1,
            'Unidade' => 'Kg',
            'Peso' => 1,
            'preco_unitario' => 10,
            'preco_total' => 10,
        ], $overrides));

        app(\App\Application\Mercadoria\Services\MercadoriaAgrupamentoService::class)->addOrUpdate($mercadoria);

        return $mercadoria;
    }
}
