<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesInvoice;
use App\Models\Recibo;
use App\Models\ReciboFactura;
use App\Models\MetodoPagamento;
use App\Models\TipoRecibo;
use App\Models\SalesStatus;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PagamentoController extends Controller
{

    public function ViewPagamento($id){

        // Verificar se o usuario que está em sessão pertence a empresa em sessão
        $salesInvoice = SalesInvoice::with('Customer', 'InvoiceType', 'salesdoctotal', 'salesstatus.salesInvoice')->where('empresa_id', $this->empresa->id)->findOrFail($id);

        // Buscar outras faturas em aberto do mesmo cliente (excluindo a atual)
        $outrasFaturas = SalesInvoice::with('salesdoctotal')->where('customer_id', $salesInvoice->customer_id)->where('id', '!=', $salesInvoice->id)->get();

        $meios = MetodoPagamento::all();
        $tipoRecibo = TipoRecibo::all();

         // Verificar se a fatura já está totalmente paga

        if ($salesInvoice->due_amount <= 0) {
            return redirect()->route('documentos.show', $id)->with('info', 'Esta fatura já foi totalmente paga.');
        }

        return view('Documentos.pagamento', compact('salesInvoice', 'outrasFaturas', 'meios', 'tipoRecibo'));
    }

    public function efetuarPagamento(Request $request, $id)
    {
        $request->validate([
            // Tipo pagamento por defeito é 'RG' - Recibo Geral
            'tipo_pagamento' => 'nullable|string',
            'valor_pago' => 'required|numeric|min:0.01',
            'desconto' => 'nullable|numeric|min:0',
            'metodo_pagamento' => 'nullable|string',
            'data_pagamento' => 'required|date',
            'descricao_pagamento' => 'nullable|string',
            // Buscar dados das faturas adicionais pagas que vem com array em formato JSON
            'dadosfacturas' => 'nullable|string',
            
        ]);

        DB::beginTransaction();

        // Decodificar JSON das faturas incluídas no pagamento
        $facturasArray = json_decode($request->dadosfacturas, true);

        if (!is_array($facturasArray) || count($facturasArray) == 0) {
            return redirect()->back()->withErrors(['error' => 'Nenhuma fatura válida foi selecionada.']);
        }

        // Busca o ID do Tipo de Pagamento
        $tipoID = TipoRecibo::where('Code', $request->tipo_pagamento)->first();

        try {
            $fatura = SalesInvoice::with('salesdoctotal')->findOrFail($id);

            $valorPago = (float) $request->valor_pago;

            // 🔹 Criar número de recibo
            // Executar o procedimento armazenado para obter o próximo número de fatura
            $numeroRecibo = DB::select("CALL GenerateReceiptNo(?,?)", [$tipoID->id, $this->empresa->id]);

            // 🔹 Criar recibo (cabeçalho)
            $recibo = Recibo::create([
                'debito_total' => $fatura->gross_total,
                'credito_total' => $valorPago,
                'recibo_no' => $numeroRecibo[0]->ReceiptNo,
                'tipo_reciboID' => $tipoID->id,
                'periodo_contabil' => now()->format('Y-m'),
                'transacaoID' => uniqid('RC'),
                'data_emissao_recibo' => now()->toDateString(),
                'descricao_pagamento' => $request->descricao_pagamento,
                'systemID' => uniqid('SYS'),
                'estado_pagamento' => 'N', // N = normal
                'data_hora_estado' => now(),
                'origem_recibo' => 'P', // P = proveniente de pagamento
                'meio_pagamento' => $request->metodo_pagamento,
                'montante_pagamento' => $valorPago,
                'data_pagamento' => $request->data_pagamento,
                'sourceID' => Auth::user()->id,
                'customer_id' => $fatura->customer_id,
                'empresa_id' => Auth::user()->empresas->first()->id,
            ]);

            // 🔹 Criar linha do recibo (ligação à fatura) que vai ser tratado com o array

            foreach ($facturasArray as $index => $facturaItem) {
                // Buscar fatura
                $faturaItem = SalesInvoice::where('invoice_no', $facturaItem['factura_no'])->firstOrFail();
                $totais = $faturaItem->salesdoctotal;

                $valorPagoItem = (float) $facturaItem['valor_divida'];
                $descontoItem = (float) ($facturaItem['desconto_documento'] ?? 0);

                ReciboFactura::create([
                    'reciboID' => $recibo->id,
                    'linha_number' => $index + 1,
                    'documentoID' => $faturaItem->id,
                    'desconto_documento' => $descontoItem,
                    'valor_em_aberto' => $totais->due_amount,
                    'valor_liquidado' => $valorPagoItem,
                ]);

                // Atualizar factura
                $novoMontantePago = $totais->montante_pagamento + $valorPagoItem;

                // Guardar
                $totais->montante_pagamento = $novoMontantePago;
                $totais->data_pagamento = now();
                $totais->save();

                // ========================
                // 3) ATUALIZAR ESTADO DA FATURA (SAF-T)
                // ========================
                $estado = $novoSaldo <= 0 ? 'N' : 'A';  
                // N = Normal (pago)
                // A = Aberto / em dívida

                SalesStatus::updateOrCreate(
                    ['documentoID' => $faturaItem->id],
                    [
                        'invoice_status'      => $estado,
                        'invoice_status_date' => now(),
                        'detalhe'             => "Atualizado automaticamente pelo recibo nº {$recibo->recibo_no}",
                    ]
                );
            }

            DB::commit();

            return redirect()->route('documentos.show', $fatura->id)->with('success', 'Pagamento registado e recibo emitido com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Erro ao processar o pagamento: ' . $e->getMessage()]);
        }
    }
}

