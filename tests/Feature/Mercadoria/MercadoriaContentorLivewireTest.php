<?php

declare(strict_types=1);

namespace Tests\Feature\Mercadoria;

use App\Application\Mercadoria\Actions\CriarMercadoriaAction;
use App\Application\Mercadoria\DTOs\MercadoriaData;
use App\Livewire\Mercadorias\CreateForm;
use App\Models\Contentor;
use App\Models\Mercadoria;
use App\Models\PautaAduaneira;
use App\Models\Subcategoria;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

final class MercadoriaContentorLivewireTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_create_form_creates_mercadoria_and_syncs_selected_process_contentor(): void
    {
        [$user, $empresa, $processo, $subcategoria, $pauta, $contentor] = $this->context('MER-CREATE-CONT');
        $this->actingAs($user);

        Livewire::test(CreateForm::class, ['context' => 'processo', 'parentId' => $processo->id])
            ->set('form.subcategoria_id', $subcategoria->id)
            ->set('form.codigo_aduaneiro', $pauta->codigo)
            ->set('form.descricao', 'Mercadoria com contentor')
            ->set('form.quantidade', 2)
            ->set('form.peso', 10)
            ->set('form.unidade', 'Kg')
            ->set('form.preco_unitario', 5)
            ->set('form.contentor_ids', [(string) $contentor->id])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('mercadoriaCreated');

        $mercadoria = Mercadoria::query()->where('Fk_Importacao', $processo->id)->firstOrFail();
        self::assertDatabaseHas('contentor_mercadoria', [
            'contentor_id' => $contentor->id,
            'mercadoria_id' => $mercadoria->id,
        ]);
        self::assertSame($empresa->id, $contentor->fresh()->empresa_id);
    }

    public function test_edit_form_replaces_mercadoria_contentor_links(): void
    {
        [$user, , $processo, $subcategoria, $pauta, $firstContentor, $secondContentor] = $this->context('MER-EDIT-CONT');
        $this->actingAs($user);
        $mercadoria = app(CriarMercadoriaAction::class)->execute(MercadoriaData::fromLivewire([
            'subcategoria_id' => $subcategoria->id,
            'codigo_aduaneiro' => $pauta->codigo,
            'descricao' => 'Mercadoria editável',
            'quantidade' => 1,
            'peso' => 5,
            'unidade' => 'Kg',
            'preco_unitario' => 12,
            'contentor_ids' => [$firstContentor->id],
        ], 'processo', $processo->id));

        Livewire::test(CreateForm::class, ['context' => 'processo', 'parentId' => $processo->id])
            ->call('openEditModal', $mercadoria->id)
            ->assertSet('mode', 'edit')
            ->set('form.contentor_ids', [(string) $secondContentor->id])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('mercadoriaUpdated');

        self::assertSame([$secondContentor->id], $mercadoria->fresh()->contentores()->pluck('contentores.id')->all());
        self::assertDatabaseMissing('contentor_mercadoria', [
            'contentor_id' => $firstContentor->id,
            'mercadoria_id' => $mercadoria->id,
        ]);
    }

    private function context(string $suffix): array
    {
        [$user, $empresa] = $this->createTenant($suffix);
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, $suffix);
        $exportador = $this->createExportador($empresa, $user, $suffix);
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);
        DB::table('processos')->where('id', $processo->id)->update(['fob_total' => 0]);
        $categoryId = DB::table('categoria_aduaneira')->insertGetId([
            'nome' => 'Categoria ' . $suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $subcategoria = Subcategoria::query()->create([
            'cod_pauta' => '02',
            'descricao' => 'Subcategoria ' . $suffix,
            'categoria_id' => $categoryId,
        ]);
        $pauta = PautaAduaneira::query()->create([
            'codigo' => '0203.11.00',
            'descricao' => 'Pauta ' . $suffix,
            'uq' => 'kg', 'rg' => 0, 'sadc' => 0, 'ua' => 0,
            'requisitos' => '0', 'observacao' => '0', 'iva' => 0, 'ieq' => 0,
        ]);
        $contentors = collect(['A', 'B'])->map(fn ($suffix) => Contentor::withoutEvents(fn () => Contentor::query()->create([
            'empresa_id' => $empresa->id,
            'processo_id' => $processo->id,
            'numero' => 'CNT-' . $suffix . '-' . random_int(10000, 99999),
        ])))->values();

        return [$user, $empresa, $processo, $subcategoria, $pauta, $contentors[0], $contentors[1]];
    }
}
