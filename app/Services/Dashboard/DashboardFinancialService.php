<?php

namespace App\Services\Dashboard;

use App\Models\ContaCorrente;
use App\Models\Recibo;
use App\Models\SalesDocTotal;
use App\Models\SalesInvoice;

class DashboardFinancialService extends BaseDashboardService
{
    public function getFinancialKpis(): array
    {
        $empresaId = $this->empresaId();
        $monthStart = now()->startOfMonth();

        $facturacaoMensal = SalesDocTotal::query()
            ->join('sales_invoice', 'sales_invoice.id', '=', 'sales_document_totals.documentoID')
            ->where('sales_invoice.empresa_id', $empresaId)
            ->whereDate('sales_invoice.invoice_date', '>=', $monthStart)
            ->sum('sales_document_totals.gross_total');

        $pagamentosRecebidos = Recibo::query()
            ->where('empresa_id', $empresaId)
            ->whereDate('data_pagamento', '>=', $monthStart)
            ->sum('montante_pagamento');

        $clientesComDivida = ContaCorrente::query()
            ->join('customers', 'customers.id', '=', 'conta_correntes.cliente_id')
            ->where('customers.empresa_id', $empresaId)
            ->selectRaw('cliente_id, SUM(CASE WHEN tipo = "debito" THEN valor ELSE -valor END) as saldo')
            ->groupBy('cliente_id')
            ->havingRaw('SUM(CASE WHEN tipo = "debito" THEN valor ELSE -valor END) > 0')
            ->get()
            ->count();

        return [
            'facturacao_mensal' => (float) $facturacaoMensal,
            'pagamentos_recebidos' => (float) $pagamentosRecebidos,
            'clientes_com_divida' => (int) $clientesComDivida,
            'facturas_emitidas' => SalesInvoice::where('empresa_id', $empresaId)
                ->whereDate('invoice_date', '>=', $monthStart)
                ->count(),
        ];
    }

    public function getRevenueLast12Months(): array
    {
        $rows = SalesInvoice::query()
            ->join('sales_document_totals', 'sales_document_totals.documentoID', '=', 'sales_invoice.id')
            ->selectRaw('YEAR(invoice_date) as year, MONTH(invoice_date) as month, SUM(sales_document_totals.gross_total) as total')
            ->where('sales_invoice.empresa_id', $this->empresaId())
            ->whereDate('invoice_date', '>=', now()->startOfMonth()->subMonths(11))
            ->groupByRaw('YEAR(invoice_date), MONTH(invoice_date)')
            ->orderByRaw('YEAR(invoice_date), MONTH(invoice_date)')
            ->get();

        return [
            'labels' => $rows->map(fn ($row) => sprintf('%02d/%s', $row->month, substr((string) $row->year, -2)))->all(),
            'series' => $rows->pluck('total')->map(fn ($value) => round((float) $value, 2))->all(),
        ];
    }

    public function getTopClientes(int $limit = 5): array
    {
        return SalesInvoice::query()
            ->join('customers', 'customers.id', '=', 'sales_invoice.customer_id')
            ->join('sales_document_totals', 'sales_document_totals.documentoID', '=', 'sales_invoice.id')
            ->where('sales_invoice.empresa_id', $this->empresaId())
            ->select('customers.CompanyName')
            ->selectRaw('SUM(sales_document_totals.gross_total) as total')
            ->groupBy('customers.id', 'customers.CompanyName')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'cliente' => $row->CompanyName,
                'total' => round((float) $row->total, 2),
            ])
            ->all();
    }

    public function getClientesComDivida(int $limit = 5): array
    {
        return ContaCorrente::query()
            ->join('customers', 'customers.id', '=', 'conta_correntes.cliente_id')
            ->where('customers.empresa_id', $this->empresaId())
            ->select('customers.CompanyName')
            ->selectRaw('SUM(CASE WHEN conta_correntes.tipo = "debito" THEN conta_correntes.valor ELSE -conta_correntes.valor END) as saldo')
            ->groupBy('customers.id', 'customers.CompanyName')
            ->havingRaw('SUM(CASE WHEN conta_correntes.tipo = "debito" THEN conta_correntes.valor ELSE -conta_correntes.valor END) > 0')
            ->orderByDesc('saldo')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'cliente' => $row->CompanyName,
                'saldo' => round((float) $row->saldo, 2),
            ])
            ->all();
    }

    public function getPaymentsSummary(): array
    {
        $empresaId = $this->empresaId();

        return [
            'recebido_total' => (float) Recibo::where('empresa_id', $empresaId)->sum('montante_pagamento'),
            'facturado_total' => (float) SalesInvoice::query()
                ->join('sales_document_totals', 'sales_document_totals.documentoID', '=', 'sales_invoice.id')
                ->where('sales_invoice.empresa_id', $empresaId)
                ->sum('sales_document_totals.gross_total'),
        ];
    }
}
