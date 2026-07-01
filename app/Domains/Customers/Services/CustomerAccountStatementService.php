<?php

namespace App\Domains\Customers\Services;

use App\Models\ContaCorrente;
use App\Models\Customer;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

final readonly class CustomerAccountStatementService
{
    public function saldo(int $customerId, ?int $empresaId = null): float
    {
        return (float) $this->baseQuery($customerId, $empresaId)
            ->get()
            ->sum(fn (ContaCorrente $transacao): float => $this->valorAssinado($transacao));
    }

    public function movimentos(int $customerId, ?int $empresaId = null): Collection
    {
        return $this->baseQuery($customerId, $empresaId)
            ->orderByDesc($this->dateColumn())
            ->orderByDesc('created_at')
            ->get();
    }

    public function movimentosRecentes(int $customerId, ?int $empresaId = null, int $limit = 5): Collection
    {
        return $this->baseQuery($customerId, $empresaId)
            ->orderByDesc($this->dateColumn())
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function totais(int $customerId, ?int $empresaId = null): array
    {
        $movimentos = $this->baseQuery($customerId, $empresaId)->get();

        $totalDebitos = $movimentos
            ->filter(fn (ContaCorrente $movimento): bool => $this->isDebito($movimento->tipo))
            ->sum(fn (ContaCorrente $movimento): float => abs((float) $movimento->valor));

        $totalCreditos = $movimentos
            ->filter(fn (ContaCorrente $movimento): bool => $this->isCredito($movimento->tipo))
            ->sum(fn (ContaCorrente $movimento): float => abs((float) $movimento->valor));

        return [
            'debitos' => (float) $totalDebitos,
            'creditos' => (float) $totalCreditos,
            'saldo' => (float) ($totalDebitos - $totalCreditos),
        ];
    }

    public function extratoQuery(
        int $customerId,
        ?int $empresaId = null,
        ?string $tipo = null,
        ?string $dataInicio = null,
        ?string $dataFim = null,
        ?string $search = null,
    ): Builder {
        $query = $this->baseQuery($customerId, $empresaId);
        $dateColumn = $this->dateColumn();

        if ($tipo !== null && $tipo !== '') {
            $query->where('tipo', $tipo);
        }

        if ($dataInicio !== null && $dataInicio !== '') {
            $query->whereDate($dateColumn, '>=', $dataInicio);
        }

        if ($dataFim !== null && $dataFim !== '') {
            $query->whereDate($dateColumn, '<=', $dataFim);
        }

        if ($search !== null && trim($search) !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('descricao', 'like', "%{$search}%")
                    ->orWhere('referencia', 'like', "%{$search}%")
                    ->orWhere('observacoes', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc($dateColumn)->orderByDesc('created_at');
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

    public function valorAssinadoTipo(string $tipo, float $valor): float
    {
        if ($this->isDebito($tipo)) {
            return abs($valor);
        }

        if ($this->isCredito($tipo)) {
            return -1 * abs($valor);
        }

        return 0.0;
    }

    private function valorAssinado(ContaCorrente $transacao): float
    {
        return $this->valorAssinadoTipo((string) $transacao->tipo, (float) $transacao->valor);
    }

    private function baseQuery(int $customerId, ?int $empresaId = null): Builder
    {
        $query = ContaCorrente::query()
            ->with('customerAvenca')
            ->where('cliente_id', $customerId);

        if ($empresaId !== null && Schema::hasColumn('conta_correntes', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        if (Schema::hasColumn('conta_correntes', 'estornado_movimento_id')) {
            $query->whereNotIn('id', function ($subquery): void {
                $subquery->select('estornado_movimento_id')
                    ->from('conta_correntes')
                    ->whereNotNull('estornado_movimento_id');
            });
        }

        return $query;
    }

    private function dateColumn(): string
    {
        return Schema::hasColumn('conta_correntes', 'data_movimento') ? 'data_movimento' : 'data';
    }

    private function isDebito(string $tipo): bool
    {
        return in_array(mb_strtolower($tipo), ['debito', 'débito', 'factura', 'fatura', 'ajuste'], true);
    }

    private function isCredito(string $tipo): bool
    {
        return in_array(mb_strtolower($tipo), ['credito', 'crédito', 'pagamento', 'estorno'], true);
    }
}
