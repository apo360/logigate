<?php

namespace Tests\Unit\Processo;

use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use PHPUnit\Framework\TestCase;

class AtualizarProcessoDTOTest extends TestCase
{
    public function test_absent_fields_are_omitted_and_explicit_nulls_and_zero_are_preserved(): void
    {
        self::assertSame([], AtualizarProcessoDTO::fromArray(['id' => 1])->toArray());
        self::assertSame(['quantidade_barris' => 0, 'certificado_origem' => null],
            AtualizarProcessoDTO::fromArray(['id' => 1, 'certificado_origem' => null, 'quantidade_barris' => 0])->toArray());
        self::assertSame(['DataPartida' => '2026-10-01'], AtualizarProcessoDTO::fromArray(['id' => 1, 'DataPartida' => '2026-10-01'])->toArray());
    }

    public function test_aliases_and_direct_command_construction_keep_their_contract(): void
    {
        self::assertSame(['N_Dar' => null, 'MarcaFiscal' => null], AtualizarProcessoDTO::fromArray(['id' => 1, 'NrDAR' => null, 'NrMarcaFiscal' => ''])->toArray());
        self::assertSame(['Estado' => 'Finalizado'], (new AtualizarProcessoDTO(id: 1, estado: EstadoProcessoEnum::FINALIZADO))->toArray());
        self::assertSame(['Estado' => 'Aberto'], AtualizarProcessoDTO::fromArray(['id' => 1, 'Estado' => EstadoProcessoEnum::ABERTO])->toArray());
    }

    public function test_exchange_confirmation_metadata_preserves_explicit_values(): void
    {
        $fields = ['cambio_origem' => 'Taxa documental', 'cambio_data' => '2026-10-01', 'cambio_confirmado' => true];
        self::assertSame($fields, AtualizarProcessoDTO::fromArray(['id' => 1] + $fields)->toArray());
        self::assertSame(['cambio_confirmado' => false], AtualizarProcessoDTO::fromArray(['id' => 1, 'cambio_confirmado' => false])->toArray());
    }
}
