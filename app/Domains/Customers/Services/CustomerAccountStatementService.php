<?php

namespace App\Domains\Customers\Services;

use App\Models\ContaCorrente;
use App\Models\Customer;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final readonly class CustomerAccountStatementService
{
    public function saldo(int $customerId): float
    {
        return (float) ContaCorrente::query()
            ->where('cliente_id', $customerId)
            ->get()
            ->sum(fn (ContaCorrente $transacao): float => $this->valorAssinado($transacao));
    }

    public function movimentos(int $customerId): Collection
    {
        return ContaCorrente::query()
            ->where('cliente_id', $customerId)
            ->orderBy('data', 'desc')
            ->get();
    }

    public function resumoPorClientes(Collection $clientes): array
    {
        $resultados = [];
        $totalSaldo = 0.0;
        $totalDividaCorrente = 0.0;
        $totalDividaVencida = 0.0;

        foreach ($clientes as $cliente) {
            $saldo = $this->saldo((int) $cliente->id);
            [$dividaCorrente, $dividaVencida] = $this->dividas((int) $cliente->id);

            if ($saldo != 0.0 || $dividaCorrente != 0.0 || $dividaVencida != 0.0) {
                $resultados[] = [
                    'cliente' => $cliente,
                    'saldo' => $saldo,
                    'dividaCorrente' => $dividaCorrente,
                    'dividaVencida' => $dividaVencida,
                ];
            }

            $totalSaldo += $saldo;
            $totalDividaCorrente += $dividaCorrente;
            $totalDividaVencida += $dividaVencida;
        }

        return compact('resultados', 'totalSaldo', 'totalDividaCorrente', 'totalDividaVencida');
    }

    private function dividas(int $customerId): array
    {
        $dividaCorrente = 0.0;
        $dividaVencida = 0.0;

        $facturas = SalesInvoice::query()->where('customer_id', $customerId)->get();

        foreach ($facturas as $invoice) {
            $grossTotal = (float) ($invoice->salesdoctotal->gross_total ?? 0);

            if ($invoice->invoice_date_end >= Carbon::now()) {
                $dividaCorrente += $grossTotal;
            } else {
                $dividaVencida += $grossTotal;
            }
        }

        return [$dividaCorrente, $dividaVencida];
    }

    private function valorAssinado(ContaCorrente $transacao): float
    {
        if ($transacao->tipo === 'credito') {
            return (float) $transacao->valor;
        }

        if ($transacao->tipo === 'debito') {
            return -1 * (float) $transacao->valor;
        }

        return (float) $transacao->valor;
    }
}
