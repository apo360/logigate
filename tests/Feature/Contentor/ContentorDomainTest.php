<?php

declare(strict_types=1);

namespace Tests\Feature\Contentor;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Contentor;
use App\Models\Mercadoria;
use App\Models\Processo;
use App\Application\Contentor\Actions\ImportarContentorAsycudaAction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Processo\ProcessoTestFixtures;
use Tests\TestCase;

final class ContentorDomainTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_contentor_schema_and_pivot_are_available(): void
    {
        self::assertTrue(Schema::hasTable('contentores'));
        self::assertTrue(Schema::hasColumns('contentores', [
            'empresa_id', 'processo_id', 'asycuda_id', 'numero', 'tipo',
            'peso_tara', 'peso_bruto', 'descarregado', 'possui_selo', 'resselado',
            'asycuda_declaration_item_id', 'deleted_at',
        ]));
        self::assertTrue(Schema::hasTable('contentor_mercadoria'));
        self::assertTrue(Schema::hasColumns('contentor_mercadoria', [
            'contentor_id', 'mercadoria_id', 'asycuda_item_id', 'asycuda_link_id', 'codigo_item',
        ]));
    }

    public function test_process_empresa_and_many_to_many_relationships_preserve_pivot_metadata(): void
    {
        [$user, $empresa] = $this->createTenant('C1REL');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'C1REL');
        $exportador = $this->createExportador($empresa, $user, 'C1REL');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);

        $mercadoriaA = $this->createMercadoria($processo, 'Mercadoria A');
        $mercadoriaB = $this->createMercadoria($processo, 'Mercadoria B');
        $mercadoriaC = $this->createMercadoria($processo, 'Mercadoria C');

        $contentorX = $this->createContentor($processo, 'MSGU9130182', '40HR');
        $contentorY = $this->createContentor($processo, 'MSCU1234567', '40HR');

        $contentorX->mercadorias()->attach($mercadoriaA->id, [
            'asycuda_item_id' => '31a99a0e-ccfb-4d01-9fdf-43550b26ce80',
            'asycuda_link_id' => 'b9da873f-e807-4735-aac5-c346d80b31b8',
            'codigo_item' => '1',
        ]);
        $contentorX->mercadorias()->attach($mercadoriaB->id, [
            'asycuda_item_id' => '865682d3-21ec-431f-9b7c-6df2c9bc93f0',
            'asycuda_link_id' => '50718885-53f1-4f01-9fdf-94f2546218f1',
            'codigo_item' => '2',
        ]);
        $contentorY->mercadorias()->attach([$mercadoriaB->id, $mercadoriaC->id]);

        self::assertSame($empresa->id, $contentorX->empresa->id);
        self::assertSame($processo->id, $contentorX->processo->id);
        self::assertEqualsCanonicalizing(
            [$contentorX->id, $contentorY->id],
            $processo->contentores()->pluck('contentores.id')->all(),
        );
        self::assertEqualsCanonicalizing(
            [$mercadoriaA->id, $mercadoriaB->id],
            $contentorX->mercadorias()->pluck('mercadorias.id')->all(),
        );
        self::assertEqualsCanonicalizing(
            [$contentorX->id, $contentorY->id],
            $mercadoriaB->contentores()->pluck('contentores.id')->all(),
        );
        self::assertSame(
            '31a99a0e-ccfb-4d01-9fdf-43550b26ce80',
            $contentorX->mercadorias()->whereKey($mercadoriaA->id)->firstOrFail()->pivot->asycuda_item_id,
        );
        self::assertSame(
            'b9da873f-e807-4735-aac5-c346d80b31b8',
            $contentorX->mercadorias()->whereKey($mercadoriaA->id)->firstOrFail()->pivot->asycuda_link_id,
        );
        self::assertSame(
            '1',
            $contentorX->mercadorias()->whereKey($mercadoriaA->id)->firstOrFail()->pivot->codigo_item,
        );
    }

    public function test_container_number_is_unique_per_process_but_reusable_in_another_process(): void
    {
        [$user, $empresa] = $this->createTenant('C1UNQ');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'C1UNQ');
        $exportador = $this->createExportador($empresa, $user, 'C1UNQ');
        $processoA = $this->createProcesso($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);
        $processoB = $this->createProcesso($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);

        $this->createContentor($processoA, 'MSGU9130182');
        $this->createContentor($processoB, 'MSGU9130182');

        try {
            $this->createContentor($processoA, 'MSGU9130182');
            self::fail('The same container number must not be duplicated within one process.');
        } catch (QueryException) {
            self::assertSame(2, Contentor::query()->where('numero', 'MSGU9130182')->count());
        }
    }

    public function test_contentor_uses_the_existing_tenant_trait(): void
    {
        self::assertContains(BelongsToTenant::class, class_uses_recursive(Contentor::class));
    }

    public function test_database_does_not_enforce_empresa_and_processo_consistency(): void
    {
        [$userA, $empresaA] = $this->createTenant('C1GAPA');
        [$userB, $empresaB] = $this->createTenant('C1GAPB');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customerA = $this->createCustomer($empresaA, $userA, 'C1GAPA');
        $exportadorA = $this->createExportador($empresaA, $userA, 'C1GAPA');
        $processoA = $this->createProcesso($empresaA, $userA, $customerA, $exportadorA, $estanciaId, $tipoProcessoId);

        $contentor = $this->createContentor($processoA, 'CROSS-TENANT-001', empresaId: $empresaB->id);

        self::assertSame($empresaB->id, $contentor->empresa_id);
        self::assertSame($empresaA->id, $contentor->processo->empresa_id);
        self::assertNotSame($contentor->empresa_id, $contentor->processo->empresa_id);
    }

    public function test_application_import_ignores_foreign_empresa_and_rejects_user_without_process_access(): void
    {
        [$userA, $empresaA] = $this->createTenant('C1APPA');
        [$userB, $empresaB] = $this->createTenant('C1APPB');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customerA = $this->createCustomer($empresaA, $userA, 'C1APPA');
        $exportadorA = $this->createExportador($empresaA, $userA, 'C1APPA');
        $processoA = $this->createProcesso($empresaA, $userA, $customerA, $exportadorA, $estanciaId, $tipoProcessoId);
        $customerB = $this->createCustomer($empresaB, $userB, 'C1APPB');
        $exportadorB = $this->createExportador($empresaB, $userB, 'C1APPB');
        $processoB = $this->createProcesso($empresaB, $userB, $customerB, $exportadorB, $estanciaId, $tipoProcessoId);
        $action = app(ImportarContentorAsycudaAction::class);

        $this->grantTenantPermissions($userA, ['processos.update', 'mercadorias.create']);
        $this->signInTenant($userA);
        $created = $action->execute($processoA->id, ['numero' => 'APP-CHECK-A', 'empresa_id' => $empresaB->id]);
        self::assertSame($processoA->empresa_id, $created->empresa_id);

        $this->signInTenant($userB);
        try {
            $action->execute($processoA->id, ['numero' => 'APP-CHECK-B', 'empresa_id' => $empresaB->id]);
            self::fail('A user from Empresa B must not create a container for Empresa A process.');
        } catch (AuthorizationException) {
            self::assertDatabaseMissing('contentores', ['numero' => 'APP-CHECK-B']);
        }

        self::assertSame($empresaB->id, $processoB->empresa_id);
    }

    public function test_contentor_soft_delete_keeps_record_and_hard_delete_cascades_only_pivot(): void
    {
        [$user, $empresa] = $this->createTenant('C1DEL');
        [$estanciaId, $tipoProcessoId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'C1DEL');
        $exportador = $this->createExportador($empresa, $user, 'C1DEL');
        $processo = $this->createProcesso($empresa, $user, $customer, $exportador, $estanciaId, $tipoProcessoId);
        $mercadoria = $this->createMercadoria($processo, 'Mercadoria para contentor');
        $contentor = $this->createContentor($processo, 'MSGU9130182');
        $contentor->mercadorias()->attach($mercadoria->id);

        Contentor::withoutEvents(fn () => $contentor->delete());
        self::assertDatabaseHas('contentores', ['id' => $contentor->id]);
        self::assertNotNull(DB::table('contentores')->where('id', $contentor->id)->value('deleted_at'));
        self::assertDatabaseHas('contentor_mercadoria', [
            'contentor_id' => $contentor->id,
            'mercadoria_id' => $mercadoria->id,
        ]);

        DB::table('contentores')->where('id', $contentor->id)->delete();
        self::assertDatabaseMissing('contentor_mercadoria', [
            'contentor_id' => $contentor->id,
            'mercadoria_id' => $mercadoria->id,
        ]);
    }

    private function createMercadoria(Processo $processo, string $descricao): Mercadoria
    {
        return Mercadoria::query()->create([
            'Fk_Importacao' => $processo->id,
            'Descricao' => $descricao,
        ]);
    }

    private function createContentor(Processo $processo, string $numero, ?string $tipo = null, ?int $empresaId = null): Contentor
    {
        return Contentor::withoutEvents(fn () => Contentor::query()->create([
            'empresa_id' => $empresaId ?? $processo->empresa_id,
            'processo_id' => $processo->id,
            'numero' => $numero,
            'tipo' => $tipo,
            'asycuda_id' => '3efc6917-44b0-4ebc-9ac9-199b935dcc98',
            'peso_bruto' => '27899.50',
            'descarregado' => false,
            'possui_selo' => false,
            'resselado' => false,
        ]));
    }
}
