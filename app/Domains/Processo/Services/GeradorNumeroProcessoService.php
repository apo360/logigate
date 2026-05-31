<?php

declare(strict_types=1);

namespace App\Domains\Processo\Services;

use App\Domains\Processo\Exceptions\NumeroProcessoDuplicadoException;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Processo\ValueObjects\NumeroProcesso;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;

final readonly class GeradorNumeroProcessoService
{
    public function __construct(private ProcessoRepositoryInterface $processos)
    {
    }

    public function gerar(int $empresaId, ?int $year = null): NumeroProcesso
    {
        $year ??= (int) now()->format('Y');

        return DB::transaction(function () use ($empresaId, $year): NumeroProcesso {
            $ultimoNumero = Processo::query()
                ->where('empresa_id', $empresaId)
                ->where('NrProcesso', 'like', "PROC-{$year}-%")
                ->lockForUpdate()
                ->orderByDesc('NrProcesso')
                ->value('NrProcesso');

            $sequencia = $ultimoNumero ? ((int) substr((string) $ultimoNumero, -6)) + 1 : 1;
            $numero = NumeroProcesso::generate($year, $sequencia);

            if ($this->processos->findByNumero((string) $numero) !== null) {
                throw NumeroProcessoDuplicadoException::comNumero((string) $numero);
            }

            return $numero;
        });
    }
}
