<?php

namespace App\Livewire\Integracoes;

use App\Models\Empresa;
use App\Models\ExternalInvoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacturacaoHongayetuFacturas extends Component
{
    use WithPagination;

    public ?Empresa $empresa = null;

    public string $status = '';

    public string $cliente = '';

    public string $numero = '';

    public string $dataInicial = '';

    public string $dataFinal = '';

    public int $valorMinimo = 0;

    public int $valorMaximo = 0;

    public string $vencimento = '';

    public string $tipoProduto = '';

    public string $tipoDocumento = '';

    public string $pagamentoStatus = '';

    public ?int $detalheFacturaId = null;

    public function mount(): void
    {
        $this->empresa = Auth::user()?->empresaAtiva();
        abort_unless($this->empresa, 403);
    }

    public function updating(string $name): void
    {
        if (in_array($name, [
            'status',
            'cliente',
            'numero',
            'dataInicial',
            'dataFinal',
            'valorMinimo',
            'valorMaximo',
            'vencimento',
            'tipoProduto',
            'tipoDocumento',
            'pagamentoStatus',
        ], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset([
            'status',
            'cliente',
            'numero',
            'dataInicial',
            'dataFinal',
            'valorMinimo',
            'valorMaximo',
            'vencimento',
            'tipoProduto',
            'tipoDocumento',
            'pagamentoStatus',
        ]);
        $this->resetPage();
    }

    public function abrirDetalhes(int $invoiceId): void
    {
        $this->detalheFacturaId = $this->query()->whereKey($invoiceId)->value('id');
    }

    public function fecharDetalhes(): void
    {
        $this->detalheFacturaId = null;
    }

    public function exportarCsv(): StreamedResponse
    {
        $invoices = $this->query()
            ->with('customer')
            ->get();

        return response()->streamDownload(function () use ($invoices): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'Tipo',
                'Numero externo',
                'Cliente',
                'NIF',
                'Total',
                'Moeda',
                'Estado',
                'Data de emissao',
                'Vencimento',
                'Referencia local',
            ], ';');

            foreach ($invoices as $invoice) {
                fputcsv($output, [
                    $invoice->document_type,
                    $invoice->external_invoice_number,
                    $invoice->customer?->CompanyName,
                    $invoice->customer?->CustomerTaxID,
                    number_format((float) $invoice->gross_total, 2, '.', ''),
                    $invoice->currency,
                    $invoice->status,
                    $invoice->issue_date?->format('Y-m-d H:i:s'),
                    $invoice->due_date?->format('Y-m-d H:i:s'),
                    $invoice->local_reference,
                ], ';');
            }

            fclose($output);
        }, 'facturas-hongayetu-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        $totais = $this->totais();

        return view('livewire.integracoes.facturacao-hongayetu-facturas', [
            'invoices' => $this->query()->paginate(15),
            'totais' => $totais,
            'detalheFactura' => $this->detalheFacturaId
                ? $this->query()->with('customer')->find($this->detalheFacturaId)
                : null,
            'statuses' => [
                ExternalInvoice::STATUS_PENDING => 'Pendente',
                ExternalInvoice::STATUS_DRAFT => 'Rascunho',
                ExternalInvoice::STATUS_ISSUED => 'Emitida',
                ExternalInvoice::STATUS_FAILED => 'Falhou',
                ExternalInvoice::STATUS_CANCELLED => 'Cancelada',
                ExternalInvoice::STATUS_PAID => 'Paga',
            ],
        ]);
    }

     /**
     * Applies only filters that correspond to attributes stored by external invoices.
     */
    private function query(): Builder
    {
        return ExternalInvoice::query()
            ->with('customer')
            ->where('empresa_id', $this->empresa->id)
            ->where('provider', ExternalInvoice::PROVIDER_HONGAYETU_FACTURACAO)
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->numero !== '', function (Builder $query): void {
                $query->where(function (Builder $nested): void {
                    $nested->where('external_invoice_number', 'like', "%{$this->numero}%")
                        ->orWhere('local_reference', 'like', "%{$this->numero}%");
                });
            })
            ->when($this->cliente !== '', function (Builder $query): void {
                $query->whereHas('customer', function (Builder $customer): void {
                    $customer->where('CompanyName', 'like', "%{$this->cliente}%")
                        ->orWhere('CustomerTaxID', 'like', "%{$this->cliente}%")
                        ->orWhere('CustomerID', 'like', "%{$this->cliente}%");
                });
            })
            ->when($this->dataInicial !== '', fn (Builder $query) => $query->whereDate('issue_date', '>=', $this->dataInicial))
            ->when($this->dataFinal !== '', fn (Builder $query) => $query->whereDate('issue_date', '<=', $this->dataFinal))
            ->when($this->valorMinimo > 0, fn (Builder $query) => $query->where('gross_total', '>=', $this->valorMinimo))
            ->when($this->valorMaximo > 0, fn (Builder $query) => $query->where('gross_total', '<=', $this->valorMaximo))
            ->when($this->tipoDocumento !== '', fn (Builder $query) => $query->where('document_type', $this->tipoDocumento))
            ->when($this->tipoProduto !== '', function (Builder $query): void {
                $query->whereHas('lines', function (Builder $line): void {
                    $line->where('description', 'like', "%{$this->tipoProduto}%")
                        ->orWhere('type', 'like', "%{$this->tipoProduto}%");
                });
            })
            ->when($this->pagamentoStatus === 'pago', fn (Builder $query) => $query->where('status', ExternalInvoice::STATUS_PAID))
            ->when($this->pagamentoStatus === 'em_divida', function (Builder $query): void {
                $query->where('status', '!=', ExternalInvoice::STATUS_PAID)
                    ->where('balance_due', '>', 0);
            })
            ->when($this->vencimento === 'vencidas', function (Builder $query): void {
                $query->whereDate('due_date', '<', today())
                    ->where('balance_due', '>', 0);
            })
            ->when($this->vencimento === 'a_vencer', function (Builder $query): void {
                $query->whereDate('due_date', '>=', today())
                    ->where('balance_due', '>', 0);
            })
            ->when($this->vencimento === '30_dias', function (Builder $query): void {
                $query->whereBetween('due_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
                    ->where('balance_due', '>', 0);
            })
            ->latest('id');
    }
    
    private function totais(): array
    {
        $totais = $this->query()
            ->reorder()
            ->selectRaw('COALESCE(SUM(gross_total), 0) as facturado')
            ->selectRaw('COALESCE(SUM(paid_total), 0) as pago')
            ->selectRaw('COALESCE(SUM(balance_due), 0) as em_divida')
            ->first();

        $facturado = (float) ($totais?->facturado ?? 0);
        $pago = (float) ($totais?->pago ?? 0);
        $emDivida = (float) ($totais?->em_divida ?? 0);

        return [
            'facturado' => $facturado,
            'pago' => $pago,
            'em_divida' => $emDivida,
            'percentagem_paga' => $facturado > 0 ? ($pago / $facturado) * 100 : 0,
            'percentagem_divida' => $facturado > 0 ? ($emDivida / $facturado) * 100 : 0,
        ];
    }
}
