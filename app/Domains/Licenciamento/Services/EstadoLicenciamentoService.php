<?php

namespace App\Domains\Licenciamento\Services;

use App\Models\Licenciamento;

final readonly class EstadoLicenciamentoService
{
    /** Set-based equivalent of estado(), scoped to the record's company. */
    public function analyticsExpression(string $alias): string
    {
        if ($alias !== 'r') {
            throw new \InvalidArgumentException('Internal analytics alias required.');
        }

        return "CASE WHEN COALESCE(r.txt_gerado,0)=0 THEN 'Por licenciar' WHEN r.txt_gerado=1 AND EXISTS (SELECT 1 FROM proc_licen_sales s WHERE s.licenciamento_id=r.id AND s.empresa_id=r.empresa_id AND s.status_fatura='paga') THEN 'Licenciado' ELSE 'Em licenciamento' END";
    }

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
