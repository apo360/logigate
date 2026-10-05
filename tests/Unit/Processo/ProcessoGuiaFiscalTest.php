<?php

namespace Tests\Unit\Processo;

use App\Models\EmolumentoTarifa;
use App\Models\Porto;
use App\Models\Processo;
use Tests\TestCase;

class ProcessoGuiaFiscalTest extends TestCase
{
    public function test_guia_fiscal_uses_costs_even_when_porto_relation_is_loaded(): void
    {
        $processo = new Processo();
        $processo->setRelation('porto', new Porto());
        $processo->setRelation('emolumentoTarifa', new EmolumentoTarifa([
            'porto' => '125.50',
            'direitos' => '20.00',
            'multas' => '4.50',
        ]));

        self::assertSame(150.0, $processo->guia_fiscal);
        self::assertSame(150.0, $processo->toArray()['guia_fiscal']);
        self::assertInstanceOf(Porto::class, $processo->porto);
    }

    public function test_guia_fiscal_without_tariff_is_zero(): void
    {
        $processo = new Processo();
        $processo->setRelation('porto', new Porto());
        $processo->setRelation('emolumentoTarifa', null);

        self::assertSame(0.0, $processo->guia_fiscal);
        self::assertSame(0.0, $processo->toArray()['guia_fiscal']);
    }
}
