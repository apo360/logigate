<?php
namespace App\Domains\Processo\Services;
use App\Domains\Processo\ValueObjects\NumeroProcesso;
final readonly class GeradorNumeroProcessoService
{
    public function gerar(int $empresaId, ?int $year = null): NumeroProcesso
    {
        $year ??= (int) now()->format('Y');
        return NumeroProcesso::generate($year, app(OperationalSequence::class)->reserve($empresaId, 'processo', $year));
    }
}
