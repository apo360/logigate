<?php

declare(strict_types=1);

namespace App\Domains\Processo\Services;

use App\Models\Processo;
use Illuminate\Support\Facades\DB;

final readonly class ContaDespachoSequencialService
{
    /**
     * Gera uma conta de despacho sequencial por ano.
     * Mantém a lógica já existente no legado (CCD-000/ANO).
     */
    public function gerarContaDespachoSequencial(): string
    {
        $anoCorrente = date('Y');

        return DB::transaction(function () use ($anoCorrente): string {
            $ultimaConta = Processo::query()
                ->whereYear('created_at', $anoCorrente)
                ->whereNotNull('ContaDespacho')
                ->orderByRaw(
                    "CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(ContaDespacho, '/', 1), '-', -1) AS UNSIGNED) DESC"
                )
                ->first();

            $sequencial = 1;

            if ($ultimaConta) {
                $parts = explode('/', (string) $ultimaConta->ContaDespacho);
                $sequencialStr = $parts[0] ?? '';

                preg_match('/\d+/', $sequencialStr, $match);
                $sequencial = isset($match[0]) ? ((int) $match[0] + 1) : 1;
            }

            return 'CCD-' . str_pad((string) $sequencial, 3, '0', STR_PAD_LEFT) . '/' . $anoCorrente;
        });
    }
}

