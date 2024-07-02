<?php

namespace App\Helpers;

use App\Models\Customer;
use Spipu\Html2Pdf\Html2Pdf;
use App\Models\Header;

class PdfHelper
{
    public static function generateHeader()
    {
        $headers = Header::all();
        return view('pdf.header', compact('headers'))->render();
    }

    public static function generateFooter()
    {
        $footer = Header::all();
        return view('pdf.footer', compact('footer'))->render();
    }

    public static function generateFacturas($processoID){

        $headers = Header::all();
        $cliente = Customer::where('Id', $processoID)->first();
        return view('pdf.facturas', compact('headers', 'cliente'))->render();
    }

    public static function generatePrint($processoID)
    {
        $pdf = new Html2Pdf();

        $header = self::generateFacturas($processoID);

        $conteudo = $header;

        // Limpe o buffer de saída antes de gerar o PDF
        ob_end_clean();

        $pdf->writeHTML($conteudo);
        $pdf->output();
    }

}