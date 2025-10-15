<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Models\ReciboFactura;
use App\Models\SalesInvoice; // Modelo da fatura de venda
use Illuminate\Http\Request;
use App\Helpers\DatabaseErrorHandler;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReciboController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Gerar o número de recibo automaticamente via procedure
        $empresaId = Auth::user()->empresas->first()->id; // ou de onde estiveres a buscar a empresa
        $tipoReciboId = $request->input('tipo_reciboID');

        $result = DB::select("CALL GenerateReceiptNo(?, ?)", [$tipoReciboId, $empresaId]);

        $receiptNo = $result[0]->ReceiptNo ?? null;

        // Criar o recibo com o número vindo do procedure
        $recibo = Recibo::create([
            'debito_total'      => $request->input('debito_total', 0),
            'credito_total'     => $request->input('credito_total', 0),
            'recibo_no'         => $receiptNo, // número sequencial vindo da SP
            'data_emissao_recibo' => Carbon::now(),
            'tipo_reciboID'     => $tipoReciboId,
            'descricao_pagamento' => $request->input('descricao_pagamento'),
            'origem_recibo'     => $request->input('origem_recibo'),
            'meio_pagamento'    => $request->input('meio_pagamento'),
            'montante_pagamento'=> $request->input('montante_pagamento'),
            'data_pagamento'    => $request->input('data_pagamento', Carbon::now()),
            'customer_id'       => $request->input('customer_id'),
            'tipo_imposto_retido' => $request->input('tipo_imposto_retido') ?? 0,
            'motivo_retencao'   => $request->input('motivo_retencao') ?? 'nulo',
            'montante_retencao' => $request->input('montante_retencao') ?? 0,
            'empresa_id'        => $empresaId,
        ]);
        // Associar faturas ao recibo
        $faturas = $request->input('facturas', []);
        foreach ($faturas as $fatura) {
            $Invoice = SalesInvoice::find($fatura['facturaID']);
            ReciboFactura::create([
                'reciboID' => $recibo->id,
                'linha_number' => $fatura['linha_number'],
                'documentoID' => $Invoice ? $Invoice->id : null,
                'desconto_documento' => $fatura['desconto_documento'] ?? 0,
                'valor_debito' => $Invoice ? $Invoice->SalesDocTotal->net_total : 0,
                'valor_credito' => $Invoice ? $Invoice->SalesDocTotal->gross_total - ($fatura['desconto_documento'] ?? 0) : 0,
            ]);
        }
        // Alterar o estado das faturas associadas
        foreach ($faturas as $fatura) {
            $invoice = SalesInvoice::find($fatura['facturaID']);
            if ($invoice) {
                $invoice->estado_pagamento = 'Pago'; // ou outro estado conforme a lógica
                $invoice->save();
            }
        }

        // Retornar resposta
        return response()->json(['success' => true, 'message' => 'Recibo criado com sucesso!', 'recibo' => $recibo]);
    }
    /**
     * Display the specified resource.
     */
    public function show(Recibo $recibo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recibo $recibo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recibo $recibo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recibo $recibo)
    {
        //
    }
}
