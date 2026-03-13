<?php

namespace App\Services;

use App\Models\Licenciamento;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;
use PHPJasper\PHPJasper;

class ReportService
{
    public function renderProcessReport(int $processoId, int $userId): string
    {
        $input = base_path('reports/hello_world.jrxml');
        $output = base_path('reports');

        $options = [
            'format' => ['pdf'],
            'locale' => 'en',
            'params' => [
                'P_processo' => $processoId,
                'P_user' => $userId,
            ],
            'db_connection' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'forge'),
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
            ],
        ];

        (new PHPJasper())->process($input, $output, $options)->execute();

        return $output . '/hello_world.pdf';
    }

    public function renderInvoiceReport(SalesInvoice $invoice, string $operatorName): string
    {
        $input = base_path('reports/facturas_geral.jrxml');
        $output = base_path('reports');

        $options = [
            'format' => ['pdf'],
            'locale' => 'en',
            'params' => [
                'despachante' => $invoice->empresa->Empresa,
                'nif' => $invoice->empresa->NIF,
                'cedula' => $invoice->empresa->Cedula,
                'telefone' => $invoice->empresa->Contacto_fixo,
                'logotipo' => public_path($invoice->empresa->Logotipo),
                'endereco' => $invoice->empresa->Endereco_completo,
                'operador' => $operatorName,
                'cliente' => $invoice->customer->CompanyName,
                'nif_cliente' => $invoice->customer->CustomerTaxID,
                'morada_cliente' => $invoice->customer->CompanyName ?? 'Desconhecido',
                'invoice_no' => $invoice->invoiceType->Descriptions . ' ' . $invoice->invoice_no,
                'data_emissao' => $invoice->invoiceType->invoice_date ? $invoice->invoiceType->invoice_date->format('d-m-Y') : '01-01-2024',
                'data_vencimento' => $invoice->invoiceType->invoice_date_end ? $invoice->invoiceType->invoice_date_end->format('d-m-Y') : '31-01-2024',
            ],
        ];

        (new PHPJasper())->process($input, $output, $options)->execute();

        return $output . '/facturas_geral.pdf';
    }

}
