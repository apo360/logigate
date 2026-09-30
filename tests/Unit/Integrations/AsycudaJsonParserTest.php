<?php

declare(strict_types=1);

namespace Tests\Unit\Integrations;

use App\Infrastructure\Integrations\Asycuda\AsycudaJsonParser;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AsycudaJsonParserTest extends TestCase
{
    private function fixture(): string
    {
        return file_get_contents(__DIR__ . '/../../Fixtures/Asycuda/observed-export.json');
    }

    public function test_real_observed_fixture_parses_and_preserves_root_sections(): void
    {
        $result = (new AsycudaJsonParser())->parse($this->fixture());

        self::assertTrue($result->success);
        self::assertSame([], $result->errors);
        self::assertSame([], $result->warnings);
        self::assertInstanceOf(stdClass::class, $result->data);

        foreach ([
            'procedure', 'offices', 'parties', 'transportInformation', 'information',
            'items', 'containers', 'mediaDocuments', 'adjustments', 'valuation',
            'declarant', 'bankAccount', 'customsSeal',
        ] as $field) {
            self::assertTrue(property_exists($result->data, $field), "Missing root field: {$field}");
        }

        $item = $result->data->items[0];
        self::assertSame('LINGUIÇA DE PORCO CONGELADA', $item->goodsDescription->commercialDescription);
        self::assertSame('BR', $item->goodsOrigin->country->country->code);
        self::assertSame('1601000000', $item->goodsDescription->tariffCode);
        self::assertSame('ADR2', $item->goodsDescription->dangerousGoods->code);
        self::assertSame(21488.7, $item->valuationDetails->itemPrice->amount);
        self::assertSame('EUR', $item->valuationDetails->itemPrice->currencyRate->currencyCode);
        self::assertSame('MEDUWE161494', $item->transportDocumentReference->waybill);
        self::assertSame('WM627R', $item->transportDocumentReference->manifest->voyageNumber);
    }

    public function test_invalid_json_returns_a_controlled_error(): void
    {
        $result = (new AsycudaJsonParser())->parse('{"items": [');

        self::assertFalse($result->success);
        self::assertNull($result->data);
        self::assertNotEmpty($result->errors);
    }

    public function test_root_must_be_a_json_object(): void
    {
        foreach (['[]', 'null', '"declaration"', '42', 'true'] as $json) {
            $result = (new AsycudaJsonParser())->parse($json);

            self::assertFalse($result->success, $json);
            self::assertNull($result->data);
        }
    }

    public function test_items_must_be_an_array_when_present(): void
    {
        $result = (new AsycudaJsonParser())->parse('{"items": {"id": 1}}');

        self::assertFalse($result->success);
        self::assertStringContainsString('items', $result->errors[0]);
    }

    public function test_unknown_root_fields_are_preserved(): void
    {
        $input = json_decode($this->fixture());
        $input->futureField = (object) ['example' => true];

        $result = (new AsycudaJsonParser())->parse(json_encode($input, JSON_THROW_ON_ERROR));

        self::assertTrue($result->success);
        self::assertTrue($result->data->futureField->example);
    }

    public function test_unknown_item_fields_are_preserved(): void
    {
        $input = json_decode($this->fixture());
        $input->items[0]->futureItemField = (object) ['value' => 'preserve-me'];

        $result = (new AsycudaJsonParser())->parse(json_encode($input, JSON_THROW_ON_ERROR));

        self::assertTrue($result->success);
        self::assertSame('preserve-me', $result->data->items[0]->futureItemField->value);
    }

    public function test_empty_external_identifiers_remain_external_values(): void
    {
        $result = (new AsycudaJsonParser())->parse($this->fixture());

        self::assertTrue($result->success);
        self::assertSame('', $result->data->parties->exporter->operatorCode);
        self::assertNull($result->data->items[0]->parties->exporter->operatorCode);
    }

    public function test_package_weight_and_supplementary_quantity_remain_separate(): void
    {
        $result = (new AsycudaJsonParser())->parse($this->fixture());
        $item = $result->data->items[0];

        self::assertSame(2589, $item->goodsPackage->number);
        self::assertSame('CT', $item->goodsPackage->packageType->code);
        self::assertSame(25890, $item->goodsWeight->net);
        self::assertSame(27899.5, $item->goodsWeight->gross);
        self::assertSame('KG', $item->supplementaryUnits[0]->code);
        self::assertSame(2589, $item->supplementaryUnits[0]->quantity);
    }

    public function test_root_and_item_adjustments_remain_separate(): void
    {
        $result = (new AsycudaJsonParser())->parse($this->fixture());

        self::assertCount(2, $result->data->adjustments);
        self::assertCount(2, $result->data->items[0]->adjustments);
        self::assertSame('BY_WEIGHT', $result->data->adjustments[0]->apportionmentMode);
        self::assertSame('NONE', $result->data->items[0]->adjustments[0]->apportionmentMode);
        self::assertSame(3510, $result->data->adjustments[0]->line->amount);
        self::assertSame(3510, $result->data->items[0]->adjustments[0]->line->amount);
    }

    public function test_container_item_uuid_relationship_is_preserved(): void
    {
        $result = (new AsycudaJsonParser())->parse($this->fixture());
        $itemId = $result->data->items[0]->id;

        self::assertSame($itemId, $result->data->containers[0]->items[0]->id);
        self::assertSame($itemId, $result->data->containers[0]->items[0]->itemId);
    }

    public function test_document_metadata_is_preserved_without_internal_file_creation(): void
    {
        $result = (new AsycudaJsonParser())->parse($this->fixture());

        self::assertCount(8, $result->data->mediaDocuments);
        self::assertSame('BL.pdf', $result->data->mediaDocuments[0]->name);
        self::assertSame('STORE', $result->data->mediaDocuments[0]->action);
        self::assertSame(278785, $result->data->mediaDocuments[0]->size);
        self::assertSame('401', $result->data->mediaDocuments[0]->code);
        self::assertSame(128, strlen($result->data->mediaDocuments[0]->hash));
        self::assertNotSame('', $result->data->mediaDocuments[0]->uid);
    }

    public function test_encoding_parsed_data_preserves_fixture_structure_and_values(): void
    {
        $parser = new AsycudaJsonParser();
        $original = $parser->parse($this->fixture());
        $encoded = json_encode($original->data, JSON_THROW_ON_ERROR);
        $roundTrip = $parser->parse($encoded);

        self::assertTrue($roundTrip->success);
        self::assertEquals($original->data, $roundTrip->data);
    }

    public function test_absent_items_is_a_warning_not_a_schema_error(): void
    {
        $result = (new AsycudaJsonParser())->parse('{"futureField": true}');

        self::assertTrue($result->success);
        self::assertNotEmpty($result->warnings);
    }
}
