<?php

declare(strict_types=1);

namespace Tests\Unit\Integrations;

use App\Infrastructure\Integrations\Asycuda\AsycudaJsonParser;
use App\Infrastructure\Integrations\Asycuda\AsycudaExportValidator;
use App\Infrastructure\Integrations\Asycuda\AsycudaProcessoMapper;
use PHPUnit\Framework\TestCase;

final class AsycudaProcessoMapperTest extends TestCase
{
    private function fixture(): object
    {
        $parsed = (new AsycudaJsonParser())->parse(file_get_contents(__DIR__ . '/../../Fixtures/Asycuda/observed-export.json'));
        self::assertTrue($parsed->success);
        return $parsed->data;
    }

    public function test_import_maps_confirmed_procedure_party_item_and_transport_references(): void
    {
        $mapped = (new AsycudaProcessoMapper())->mapImport($this->fixture());

        self::assertSame(['abrev' => 'IM', 'codigo' => '4'], $mapped->processo['regiao_aduaneira_reference']);
        self::assertSame('0000000000', $mapped->customerReference['identifier']);
        self::assertSame('ANONYMIZED EXPORTER', $mapped->exportadorReference['name']);
        self::assertSame('01049', $mapped->declarantReference['identifier']);
        self::assertSame($this->fixture()->items[0]->goodsDescription->commercialDescription, $mapped->mercadorias[0]['descricao']);
        self::assertSame('1601000000', $mapped->mercadorias[0]['ncm_hs']);
        self::assertSame('1601000000', $mapped->mercadorias[0]['ncm_hs_numero']);
        self::assertSame('1', $mapped->processo['tipo_transporte']);
        self::assertNull($mapped->processo['registo_transporte']);
        self::assertSame('WM627R', $mapped->processo['manifest_reference']['voyageNumber']);
        self::assertSame('3POLA', $mapped->processo['estancia_reference']);
        self::assertSame(['extended_code' => '4100', 'additional_code' => '000'], $mapped->processo['customs_procedure_reference']);
        self::assertSame('BR', $mapped->mercadorias[0]['origem_pais']);
    }

    public function test_import_maps_container_uuid_links_to_item_by_external_uuid(): void
    {
        $mapped = (new AsycudaProcessoMapper())->mapImport($this->fixture());

        self::assertSame('3efc6917-44b0-4ebc-9ac9-199b935dcc98', $mapped->contentores[0]['external_id']);
        self::assertSame('MSGU9130182', $mapped->contentores[0]['numero']);
        self::assertSame('FULL', $mapped->contentores[0]['indicador_carga']);
        self::assertSame('31a99a0e-ccfb-4d01-9fdf-43550b26ce80', $mapped->contentorMercadorias[0]['mercadoria_external_id']);
        self::assertSame(0, $mapped->contentorMercadorias[0]['mercadoria_index']);
        self::assertSame('31a99a0e-ccfb-4d01-9fdf-43550b26ce80', $mapped->contentorMercadorias[0]['asycuda_link_id']);
        self::assertSame('1', $mapped->contentorMercadorias[0]['codigo_item']);
        self::assertFalse($mapped->contentorMercadorias[0]['unresolved']);
    }

    public function test_multiple_container_item_links_are_many_to_many_and_unresolved_ids_are_reported(): void
    {
        $source = json_decode(json_encode($this->fixture(), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $itemB = $source['items'][0];
        $itemB['id'] = 'fdd2c29a-68eb-4ddb-b7c6-5486f27c9042';
        $itemB['itemNumber'] = 2;
        $source['items'][] = $itemB;
        $source['containers'][0]['items'][] = ['id' => '46a359c2-7025-4e4c-8b1e-7f70410b9877', 'itemId' => $itemB['id'], 'code' => '2'];
        $source['containers'][] = [
            'id' => '7bc08b15-3883-4057-9c90-83c755870c5d',
            'number' => 'MSCU1234567',
            'items' => [['id' => '5dd4d782-ad2e-4e59-9fb0-e17fd9b63ea1', 'itemId' => $source['items'][0]['id'], 'code' => '1']],
        ];
        $source['containers'][1]['items'][] = ['id' => '7e84b861-28b8-4d86-8272-3f11ec7c340f', 'itemId' => 'd7ae5011-8671-4a4c-b92a-0bce0307e888', 'code' => 'missing'];

        $mapped = (new AsycudaProcessoMapper())->mapImport($source);

        self::assertCount(2, $mapped->mercadorias);
        self::assertCount(2, $mapped->contentores);
        self::assertCount(4, $mapped->contentorMercadorias);
        self::assertSame([0, 1, 0, null], array_column($mapped->contentorMercadorias, 'mercadoria_index'));
        self::assertTrue($mapped->contentorMercadorias[3]['unresolved']);
        self::assertNotEmpty($mapped->warnings);
    }

    public function test_import_preserves_documents_valuation_adjustments_and_unknown_fields_without_persistence(): void
    {
        $source = json_decode(json_encode($this->fixture(), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $source['futureField'] = ['keep' => true];
        $mapped = (new AsycudaProcessoMapper())->mapImport($source);

        self::assertCount(8, $mapped->documentos);
        self::assertSame('401', $mapped->documentos[0]['code']);
        self::assertSame('BL.pdf', $mapped->documentos[0]['name']);
        self::assertSame($source['valuation'], $mapped->financial['valuation']);
        self::assertSame($source['adjustments'], $mapped->financial['adjustments']);
        self::assertSame($source['items'][0]['adjustments'], $mapped->mercadorias[0]['ajustes']);
        self::assertTrue($mapped->unmapped['root']['futureField']['keep']);
    }

    public function test_export_reuses_serialization_uuid_between_items_and_container_links(): void
    {
        $mapped = (new AsycudaProcessoMapper())->mapExport(
            ['tipo_declaracao' => ['abrev' => 'IM', 'codigo' => '4'], 'TipoTransporte' => '1', 'registo_transporte' => 'WM627R'],
            ['NIF' => '123', 'Empresa' => 'Declarant', 'Cedula' => 'CED-1', 'Email' => 'declarant@example.test'],
            ['CustomerTaxID' => '12345', 'CompanyName' => 'Customer'],
            ['ExportadorTaxID' => '67890', 'Exportador' => 'Exporter', 'Endereco' => 'Luanda', 'Pais' => 'AO'],
            [['id' => 101, 'Descricao' => 'Good', 'codigo_aduaneiro' => '1601000000']],
            [['numero' => 'MSCU1234567', 'asycuda_id' => null, 'mercadorias' => [['mercadoria_id' => 101, 'asycuda_link_id' => null, 'codigo_item' => '1']]]],
        );

        self::assertSame('IM', $mapped['procedure']['modelCode']);
        self::assertSame('4', $mapped['procedure']['generalProcedureCode']);
        self::assertSame('12345', $mapped['parties']['consignee']['operatorCode']);
        self::assertSame('67890', $mapped['parties']['exporter']['operatorCode']);
        self::assertSame('123', $mapped['declarant']['operatorCode']);
        self::assertSame($mapped['items'][0]['id'], $mapped['containers'][0]['items'][0]['itemId']);
        self::assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $mapped['items'][0]['id']);
        self::assertNotSame('101', $mapped['items'][0]['id']);
    }

    public function test_export_mapping_passes_observed_structural_profile_and_retains_office_path(): void
    {
        $mapped = (new AsycudaProcessoMapper())->mapExport(
            ['tipo_declaracao' => ['abrev' => 'IM', 'codigo' => '4'], 'TipoTransporte' => '1', 'transport_identifier' => 'MSC SHIP', 'estancia' => ['code' => '3POLA']],
            ['NIF' => '123', 'Empresa' => 'Declarant'], ['CustomerTaxID' => '12345', 'CompanyName' => 'Customer'],
            ['ExportadorTaxID' => '67890', 'Exportador' => 'Exporter'],
            [['id' => 101, 'Descricao' => 'Good', 'codigo_aduaneiro' => '1601000000']], [],
        );
        $validation = (new AsycudaExportValidator())->validate($mapped);
        self::assertSame([], $validation['errors']);
        self::assertSame('3POLA', $mapped['offices']['clearance']['code']);
        self::assertSame('MSC SHIP', $mapped['transportInformation']['arrivalIdentity']['identifier']);
        self::assertArrayNotHasKey('schemaVersion', $mapped);
    }

    public function test_observed_fixture_import_export_round_trip_preserves_confirmed_invariants(): void
    {
        $source = $this->fixture();
        $import = (new AsycudaProcessoMapper())->mapImport($source);
        $items = array_map(fn ($item) => [
            'id' => $item['external_id'], 'asycuda_id' => $item['external_id'], 'itemNumber' => $item['item_number'],
            'Descricao' => $item['descricao'], 'codigo_aduaneiro' => $item['codigo_aduaneiro'],
        ], $import->mercadorias);
        $containers = array_map(fn ($container) => [
            'asycuda_id' => $container['external_id'], 'numero' => $container['numero'], 'tipo' => $container['tipo'],
            'mercadorias' => array_map(fn ($link) => [
                'mercadoria_id' => $items[$link['mercadoria_index']]['id'] ?? null,
                'asycuda_link_id' => $link['asycuda_link_id'], 'codigo_item' => $link['codigo_item'],
            ], array_values(array_filter($import->contentorMercadorias, fn ($link) => $link['contentor_external_id'] === $container['external_id'] && $link['mercadoria_index'] !== null))),
        ], $import->contentores);
        $output = (new AsycudaProcessoMapper())->mapExport(
            ['tipo_declaracao' => $import->processo['regiao_aduaneira_reference'], 'TipoTransporte' => $import->processo['tipo_transporte'],
                'registo_transporte' => $import->processo['registo_transporte'], 'manifest_reference' => $import->processo['manifest_reference'],
                'transport_identifier' => $source->transportInformation->arrivalIdentity->identifier,
                'nacionalidade_transporte' => $source->transportInformation->arrivalIdentity->nationalityCode,
                'estancia' => ['code' => $import->processo['estancia_reference']]],
            [], [], [], $items, $containers,
        );

        self::assertSame($source->procedure->modelCode, $output['procedure']['modelCode']);
        self::assertSame($source->procedure->generalProcedureCode, $output['procedure']['generalProcedureCode']);
        self::assertSame($source->items[0]->goodsDescription->commercialDescription, $output['items'][0]['goodsDescription']['commercialDescription']);
        self::assertSame($source->items[0]->goodsDescription->tariffCode, $output['items'][0]['goodsDescription']['tariffCode']);
        self::assertSame($source->transportInformation->inlandTransportMode->code, $output['transportInformation']['inlandTransportMode']['code']);
        self::assertSame($source->items[0]->transportDocumentReference->manifest->voyageNumber, $output['items'][0]['transportDocumentReference']['manifest']['voyageNumber']);
        self::assertSame($source->containers[0]->number, $output['containers'][0]['number']);
        self::assertSame($source->containers[0]->type, $output['containers'][0]['type']);
        self::assertSame($output['items'][0]['id'], $output['containers'][0]['items'][0]['itemId']);
    }
}
