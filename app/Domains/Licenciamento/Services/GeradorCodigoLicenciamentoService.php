<?php

namespace App\Domains\Licenciamento\Services;

use App\Models\Licenciamento;
use Illuminate\Support\Facades\DB;

final readonly class GeradorCodigoLicenciamentoService
{
    public function gerar(int $empresaId): string
    {
        return DB::transaction(function () use ($empresaId): string {
            $ultimoCodigo = Licenciamento::query()
                ->where('empresa_id', $empresaId)
                ->whereNotNull('codigo_licenciamento')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('codigo_licenciamento');

            $sequencial = 1;

            if ($ultimoCodigo) {
                preg_match('/(\d+)(?:\/\d{2})?$/', (string) $ultimoCodigo, $match);
                $sequencial = isset($match[1]) ? ((int) $match[1] + 1) : 1;
            }

            return 'HYLC-' . str_pad((string) $empresaId, 3, '0', STR_PAD_LEFT)
                . '-' . str_pad((string) $sequencial, 5, '0', STR_PAD_LEFT)
                . '/' . now()->format('y');
        });
    }
}
