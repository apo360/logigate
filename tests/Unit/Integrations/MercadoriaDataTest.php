<?php

declare(strict_types=1);

namespace Tests\Unit\Integrations;

use App\Application\Mercadoria\DTOs\MercadoriaData;
use PHPUnit\Framework\TestCase;

final class MercadoriaDataTest extends TestCase
{
    public function test_contentor_ids_are_normalized_and_excluded_from_model_attributes(): void
    {
        $data = MercadoriaData::fromLivewire([
            'contentor_ids' => [null, '2', 2, 'invalid', 0, -4, '7', 3.2],
            'quantidade' => 1,
            'unidade' => 'Kg',
        ], 'processo', 10);

        self::assertSame([2, 7], $data->contentorIds);
        self::assertArrayNotHasKey('contentor_ids', $data->toModelAttributes());
    }

    public function test_licenciamento_payload_keeps_contentor_ids_out_of_model_mapping(): void
    {
        $data = MercadoriaData::fromLivewire(['contentor_ids' => [9]], 'licenciamento', 10);

        self::assertSame([9], $data->contentorIds);
        self::assertArrayNotHasKey('contentor_ids', $data->toModelAttributes());
    }
}
