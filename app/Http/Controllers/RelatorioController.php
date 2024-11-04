<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Processo;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPJasper\PHPJasper;

class RelatorioController extends Controller
{

    public function generateReport($ProcessoID)
    {

        //auth()->user()->empresa->Empresa;

        $input = base_path('reports/hello_world.jrxml'); // Certifique-se de que este arquivo existe
        $output = base_path('reports');

        // Configuração da conexão com o banco de dados
        $connection = [
            'driver' => 'mysql', // Substitua pelo driver apropriado
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
        ];

        $options = [
            'format' => ['pdf'],
            'locale' => 'en',
            'params' => [
                'P_processo' => $ProcessoID,
                'P_user' => Auth::user()->id,
            ],
            'db_connection' => $connection,
        ];


        $jasper = new PHPJasper();

        $jasper->process(
            $input,
            $output,
            $options
        )->execute();

        $file = $output . '/hello_world.pdf';

        if (!file_exists($file)) {
            abort(404);
        }

        return response()->file($file);
    }
    
    public function generateInvoices($invoiceNo){

        $invoices = SalesInvoice::findOrFail($invoiceNo);

        // Caminho completo para o template .jasper
        $input = base_path('reports/facturas_geral.jrxml'); // Certifique-se de que este arquivo existe
        $output = base_path('reports');
    
        // Definir os parâmetros
        $options = [
            'format' => ['pdf'],
            'locale' => 'en',
            'params' => [
                // Dados Empresa
                'despachante' => $invoices->empresa->Empresa,
                'nif' => $invoices->empresa->NIF,
                'cedula' => $invoices->empresa->Cedula,
                'telefone' => $invoices->empresa->Contacto_fixo,
                'logotipo' => public_path($invoices->empresa->Logotipo),
                'endereco' => $invoices->empresa->Endereco_completo,
                'operador' => Auth::user()->name,

                // Dados do cliente
                'cliente' => $invoices->customer->CompanyName,
                'nif_cliente' => $invoices->customer->CustomerTaxID,
                'morada_cliente' => $invoices->customer->CompanyName ?? 'Desconhecido',

                // Dados Facturas
                'invoice_no' => $invoices->invoiceType->Descriptions. ' ' .$invoices->invoice_no,
                'data_emissao' => $invoices->invoiceType->invoice_date ? $invoices->invoiceType->invoice_date->format('d-m-Y') : '01-01-2024',
                'data_vencimento' => $invoices->invoiceType->invoice_date_end ? $invoices->invoiceType->invoice_date_end->format('d-m-Y') : '31-01-2024',
                 // Converter para string se o template espera string
                
            ],
        ];

        $jasper = new PHPJasper();

        $jasper->process(
            $input,
            $output,
            $options
        )->execute();

        $file = $output . '/facturas_geral.pdf';

        if (!file_exists($file)) {
            abort(404);
        }

        return response()->file($file);

    }

}
