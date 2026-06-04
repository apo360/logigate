<?php

namespace App\Domains\Licenciamento\Services;

use App\Models\Licenciamento;

final readonly class EstadoLicenciamentoService
{
    public function estado(Licenciamento $licenciamento): string
    {
        if ((int) $licenciamento->txt_gerado === 0) {
            return 'Por licenciar';
        }

        if ((int) $licenciamento->txt_gerado === 1 && $licenciamento->procLicenFaturas->where('status_fatura', 'paga')->isNotEmpty()) {
            return 'Licenciado';
        }

        return 'Em licenciamento';
    }
}
