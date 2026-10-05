<?php

declare(strict_types=1);

namespace Tests\Feature\Processo;

use App\Application\Processo\Actions\ImportarDeclaracaoAsycudaAction;
use App\Application\Processo\Actions\PrepararImportacaoAsycudaAction;
use App\Application\Processo\DTOs\AsycudaImportResolvedData;
use App\Infrastructure\Integrations\Asycuda\AsycudaJsonParser;
use App\Infrastructure\Integrations\Asycuda\AsycudaProcessoMapper;
use App\Models\Estancia;
use App\Models\PautaAduaneira;
use App\Models\RegiaoAduaneira;
use App\Models\TipoTransporte;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ImportarDeclaracaoAsycudaTest extends TestCase
{
    use DatabaseTransactions;
    use ProcessoTestFixtures;

    public function test_preview_resolves_tenant_references_without_creating_entities(): void
    {
        [$user, $empresa, $refs, $mapped] = $this->context();
        $customerCount = \App\Models\Customer::query()->count();
        $exporterCount = \App\Models\Exportador::query()->count();
        $this->signInTenant($user);

        $preview = app(PrepararImportacaoAsycudaAction::class)->execute($mapped, $refs);

        self::assertTrue($preview->canImport());
        self::assertSame($empresa->id, $preview->resolved['empresa_id']);
        self::assertSame($refs['customer_id'], $preview->resolved['customer_id']);
        self::assertSame($refs['exportador_id'], $preview->resolved['exportador_id']);
        self::assertSame(21488.7, $preview->mapped['financial']['policy']['external_invoice_total']['amount']);
        self::assertSame(0, \App\Models\Customer::query()->count() - $customerCount);
        self::assertSame(0, \App\Models\Exportador::query()->count() - $exporterCount);

        $withoutInternalPrice = $refs;
        unset($withoutInternalPrice['item_prices']);
        $unresolved = app(PrepararImportacaoAsycudaAction::class)->execute($mapped, $withoutInternalPrice);
        self::assertContains('item_prices.0', $unresolved->requiresResolution);
        self::assertSame(21488.7, $unresolved->mapped['mercadorias'][0]['external_item_price']['amount']);
    }

    public function test_confirm_creates_new_process_items_containers_and_uuid_based_pivot(): void
    {
        [$user, $empresa, $refs, $mapped] = $this->context();
        $this->signInTenant($user);
        Storage::fake('s3');

        $preview = app(PrepararImportacaoAsycudaAction::class)->execute($mapped, $refs);
        $process = app(ImportarDeclaracaoAsycudaAction::class)->execute(new AsycudaImportResolvedData($preview->mapped, $preview->resolved));

        self::assertSame($empresa->id, $process->empresa_id);
        self::assertStringStartsWith('PROC-', $process->NrProcesso);
        self::assertCount(1, $process->mercadorias()->get());
        self::assertCount(1, $process->contentores()->get());
        self::assertDatabaseHas('contentores', ['processo_id' => $process->id, 'empresa_id' => $empresa->id, 'numero' => 'MSGU9130182']);
        self::assertDatabaseHas('contentor_mercadoria', [
            'contentor_id' => $process->contentores()->value('id'),
            'mercadoria_id' => $process->mercadorias()->value('id'),
            'asycuda_item_id' => $mapped['mercadorias'][0]['external_id'],
            'asycuda_link_id' => $mapped['contentorMercadorias'][0]['asycuda_link_id'],
            'codigo_item' => '1',
        ]);
        self::assertSame(0.25, (float) $process->mercadorias()->firstOrFail()->preco_unitario);
        self::assertSame(647.25, (float) $process->mercadorias()->firstOrFail()->preco_total);
        // Item totals do not replace an unconfirmed declared FOB.
        self::assertSame(0.0, (float) $process->fob_total);
        self::assertSame(21488.7, $mapped['financial']['policy']['external_invoice_total']['amount']);
        self::assertNotSame((float) $mapped['financial']['policy']['external_invoice_total']['amount'], (float) $process->fob_total);
        self::assertSame(1, $empresa->customers()->count());
        self::assertSame(1, $empresa->exportadors()->count());
    }

    public function test_invalid_external_item_uuid_rolls_back_all_import_rows(): void
    {
        [$user, , $refs, $mapped] = $this->context();
        $this->signInTenant($user);
        Storage::fake('s3');
        $mapped['contentorMercadorias'][0]['mercadoria_external_id'] = 'missing-item-uuid';
        $before = [
            'processos' => \App\Models\Processo::query()->count(),
            'mercadorias' => \App\Models\Mercadoria::query()->count(),
            'contentores' => \App\Models\Contentor::query()->count(),
        ];
        $preview = app(PrepararImportacaoAsycudaAction::class)->execute($mapped, $refs);

        try {
            app(ImportarDeclaracaoAsycudaAction::class)->execute(new AsycudaImportResolvedData($preview->mapped, $preview->resolved));
            self::fail('An unresolved external item UUID must roll back the import.');
        } catch (ValidationException) {
            self::assertSame($before['processos'], \App\Models\Processo::query()->count());
            self::assertSame($before['mercadorias'], \App\Models\Mercadoria::query()->count());
            self::assertSame($before['contentores'], \App\Models\Contentor::query()->count());
        }
    }

    public function test_company_cannot_resolve_or_import_using_another_company_customer_and_exporter(): void
    {
        [$userA, $empresaA, $refs, $mapped] = $this->context();
        [$userB, $empresaB] = $this->createTenant('ASYCUDA-FOREIGN-' . random_int(100, 999));
        $customerB = $this->createCustomer($empresaB, $userB, 'ASYCUDA-FOREIGN-C-' . random_int(100, 999));
        $exporterB = $this->createExportador($empresaB, $userB, 'ASYCUDA-FOREIGN-E-' . random_int(100, 999));
        $this->signInTenant($userA);

        $crossTenantChoices = array_merge($refs, [
            'customer_id' => $customerB->id,
            'exportador_id' => $exporterB->id,
        ]);
        $preview = app(PrepararImportacaoAsycudaAction::class)->execute($mapped, $crossTenantChoices);

        self::assertContains('customer_id', $preview->requiresResolution);
        self::assertContains('exportador_id', $preview->requiresResolution);
        self::assertFalse($preview->canImport());

        $before = \App\Models\Processo::query()->count();
        try {
            $forgedResolutions = $preview->resolved;
            $forgedResolutions['customer_id'] = $customerB->id;
            $forgedResolutions['exportador_id'] = $exporterB->id;
            app(ImportarDeclaracaoAsycudaAction::class)->execute(new AsycudaImportResolvedData($preview->mapped, $forgedResolutions));
            self::fail('The import action must reject foreign tenant references even when resolutions are forged.');
        } catch (AuthorizationException) {
            self::assertSame($before, \App\Models\Processo::query()->count());
        }
    }

    private function context(): array
    {
        [$user, $empresa] = $this->createTenant('ASYCUDA-' . random_int(100, 999));
        $this->grantTenantPermissions($user, ['processos.create', 'processos.update', 'mercadorias.create']);
        [$estanciaId] = $this->createLookupData();
        $customer = $this->createCustomer($empresa, $user, 'ASYCUDA-' . random_int(100, 999));
        $exportador = $this->createExportador($empresa, $user, 'ASYCUDA-' . random_int(100, 999));
        $estancia = Estancia::query()->find($estanciaId);
        $estancia->forceFill(['cod_estancia' => '3POLA', 'desc_estancia' => 'Porto de Luanda'])->save();
        $tipoId = \Illuminate\Support\Facades\DB::table('regiao_aduaneiras')->insertGetId([
            'codigo' => '4', 'abrev' => 'IM', 'descricao' => 'Importação Definitiva',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $tipo = RegiaoAduaneira::query()->findOrFail($tipoId);
        TipoTransporte::query()->firstOrCreate(['id' => 1], ['descricao' => 'Maritimo']);
        $pauta = PautaAduaneira::query()->create([
            'codigo' => '1601000000', 'descricao' => 'Preparações de carne', 'uq' => 'kg',
            'rg' => '0', 'sadc' => '0', 'ua' => '0', 'requisitos' => '0', 'observacao' => '0', 'iva' => '0', 'ieq' => '0',
        ]);
        $parsed = app(AsycudaJsonParser::class)->parse(file_get_contents(base_path('tests/Fixtures/Asycuda/observed-export.json')));
        self::assertTrue($parsed->success);
        $mapped = json_decode(json_encode(get_object_vars(app(AsycudaProcessoMapper::class)->mapImport($parsed->data)), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $mapped['processo']['paises']['origem'] = null;
        $mapped['processo']['paises']['destino'] = null;
        $mapped['processo']['paises']['nacionalidade_transporte'] = null;

        return [$user, $empresa, [
            'customer_id' => $customer->id,
            'exportador_id' => $exportador->id,
            'tipo_processo_id' => $tipo->id,
            'estancia_id' => $estancia->id,
            'tipo_transporte_id' => 1,
            'forma_pagamento' => 'RD',
            'codigo_banco' => '0055',
            'moeda' => 'EUR',
            'pauta_ids' => [0 => $pauta->id],
            'item_prices' => [0 => 0.25],
        ], $mapped];
    }
}
