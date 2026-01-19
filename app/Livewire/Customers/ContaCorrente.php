<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ContaCorrente as CC;
use App\Models\Customer;
use App\Models\Processo;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\Auth;

class ContaCorrente extends Component
{
    use WithPagination;

    public $customerId;
    public $customer;
    
    // Form fields
    public $form = [
        'data_movimento' => '',
        'descricao' => '',
        'referencia' => '',
        'tipo_movimento' => 'Fatura',
        'valor' => '',
        'documento_id' => null,
        'processo_id' => null,
        'observacoes' => ''
    ];
    
    // Filters
    public $search = '';
    public $tipo = '';
    public $data_inicio = '';
    public $data_fim = '';
    public $perPage = 20;
    
    // State
    public $editingId = null;
    public $showForm = false;
    public $showExtratoModal = false;
    public $showSaldoModal = false;
    
    // Calculated values
    public $saldoAtual = 0;
    public $saldoInicial = 0;
    public $totalCreditos = 0;
    public $totalDebitos = 0;
    
    // Options
    public $tiposMovimento = [
        'Fatura' => 'Fatura/Nota de Débito',
        'Pagamento' => 'Pagamento/Recibo',
        'Transferência' => 'Transferência Bancária',
        'Ajuste' => 'Ajuste/Correção',
        'Reembolso' => 'Reembolso',
        'Juros' => 'Juros/Multas',
        'Outro' => 'Outro'
    ];
    
    protected $rules = [
        'form.data' => 'required|date',
        'form.descricao' => 'required|string|max:255',
        'form.referencia' => 'nullable|string|max:100',
        'form.tipo' => 'required|in:Fatura,Pagamento,Transferência,Ajuste,Reembolso,Juros,Outro',
        'form.valor' => 'nullable|numeric|min:0',
        'form.documento_id' => 'nullable|exists:documentos,id',
        'form.processo_id' => 'nullable|exists:processos,id',
        'form.observacoes' => 'nullable|string|max:1000'
    ];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = Customer::with(['empresas', 'processos', 'contaCorrente'])->findOrFail($customerId);
        
        // Set default values
        $this->form['data'] = now()->format('Y-m-d');
        
        // Calculate initial values
        $this->calculateSaldo();
    }

    public function calculateSaldo(): void
    {
        $movimentos = CC::where('cliente_id', $this->customerId)->get();

        $this->totalDebitos = $movimentos
            ->where('valor', '<', 0)
            ->sum('valor') * -1; // torna positivo

        $this->totalCreditos = $movimentos
            ->where('valor', '>', 0)
            ->sum('valor');

        $this->saldoAtual = $movimentos->sum('valor');

        $this->saldoInicial = 0; // opcional se não existir
    }


    public function save(): void
    {
        $this->validate();

        if (empty($this->form['valor'])) {
            $this->addError('form.valor', 'Informe o valor do movimento.');
            return;
        }

        $valor = match ($this->form['tipo_movimento']) {
            'Factura', 'Débito', 'Ajuste' => -abs($this->form['valor']),
            'Pagamento', 'Crédito'       => abs($this->form['valor']),
            default => throw new \Exception('Tipo inválido'),
        };

        CC::create([
            'cliente_id'    => $this->customerId,
            'data_movimento'=> $this->form['data_movimento'],
            'referencia'    => $this->form['referencia'],
            'tipo_movimento'=> $this->form['tipo_movimento'],
            'valor'         => $valor,
            'documento_id'  => $this->form['documento_id'] ?? null,
            'processo_id'   => $this->form['processo_id'] ?? null,
            'observacoes'   => $this->form['observacoes'],
        ]);

        $this->resetForm();
        $this->calculateSaldo();

        session()->flash('success', 'Movimento registado com sucesso.');
    }


    private function recalcularSaldos($movimentoId)
    {
        $movimento = CC::find($movimentoId);
        $movimentosPosteriores = CC::where('customer_id', $this->customerId)
            ->where('data_movimento', '>=', $movimento->data_movimento)
            ->where('id', '!=', $movimentoId)
            ->orderBy('data_movimento')
            ->orderBy('created_at')
            ->get();
        
        $saldoAnterior = CC::where('customer_id', $this->customerId)
            ->where(function($query) use ($movimento) {
                $query->where('data_movimento', '<', $movimento->data_movimento)
                      ->orWhere(function($q) use ($movimento) {
                          $q->where('data_movimento', $movimento->data_movimento)
                            ->where('created_at', '<', $movimento->created_at);
                      });
            })
            ->orderBy('data_movimento', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $saldo = $saldoAnterior ? $saldoAnterior->saldo : 0;
        
        foreach ($movimentosPosteriores as $mov) {
            $saldo = $saldo + $mov->valor_credito - $mov->valor_debito;
            $mov->update(['saldo' => $saldo]);
        }
    }

    private function recalcularTodosSaldos()
    {
        $movimentos = CC::where('cliente_id', $this->customerId)
            ->orderBy('data')
            ->orderBy('created_at')
            ->get();
        
        $saldo = 0;
        foreach ($movimentos as $movimento) {
            $saldo = $saldo + $movimento->valor_credito - $movimento->valor_debito;
            $movimento->update(['saldo' => $saldo]);
        }
    }

    public function edit($id)
    {
        $movimento = CC::findOrFail($id);
        
        $this->editingId = $id;
        $this->form = [
            'data_movimento' => $movimento->data_movimento->format('Y-m-d'),
            'descricao' => $movimento->descricao,
            'referencia' => $movimento->referencia,
            'tipo_movimento' => $movimento->tipo_movimento,
            'valor_debito' => $movimento->valor_debito,
            'valor_credito' => $movimento->valor_credito,
            'documento_id' => $movimento->documento_id,
            'processo_id' => $movimento->processo_id,
            'observacoes' => $movimento->observacoes
        ];
        
        $this->showForm = true;
    }

    public function delete($id)
    {
        try {
            CC::findOrFail($id)->delete();
            $this->recalcularTodosSaldos();
            $this->calculateSaldo();
            
            session()->flash('success', 'Movimento excluído com sucesso!');
        } catch (\Exception $e) {
            session()->flash('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->form = [
            'data_movimento' => now()->format('Y-m-d'),
            'descricao' => '',
            'referencia' => '',
            'tipo_movimento' => 'Fatura',
            'valor_debito' => '',
            'valor_credito' => '',
            'documento_id' => null,
            'processo_id' => null,
            'observacoes' => ''
        ];
        $this->editingId = null;
        $this->showForm = false;
    }

    public function updated($propertyName)
    {
        // Auto-preenche valores se um for alterado
        if ($propertyName === 'form.valor_debito' && !empty($this->form['valor_debito'])) {
            $this->form['valor_credito'] = '';
        }
        
        if ($propertyName === 'form.valor_credito' && !empty($this->form['valor_credito'])) {
            $this->form['valor_debito'] = '';
        }
    }

    public function gerarExtrato()
    {
        $this->showExtratoModal = true;
    }

    public function render()
    {
        $query = CC::where('cliente_id', $this->customerId)
            ->orderBy('data', 'desc')
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('descricao', 'like', "%{$this->search}%")
                  ->orWhere('referencia', 'like', "%{$this->search}%")
                  ->orWhere('observacoes', 'like', "%{$this->search}%");
            });
        }
        
        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }
        
        if ($this->data_inicio) {
            $query->where('data', '>=', $this->data_inicio);
        }
        
        if ($this->data_fim) {
            $query->where('data', '<=', $this->data_fim);
        }
        
        $movimentos = $query->paginate($this->perPage);
        
        // Get processes and invoices for dropdowns
        $processos = Processo::where('customer_id', $this->customerId)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'vinheta', 'Descricao']);
            
        $documentos = SalesInvoice::where('customer_id', $this->customerId)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'invoice_no', 'detalhes_factura']);
        
        // Calculate period totals
        $periodoTotalDebitos = $query->sum('valor');
        $periodoTotalCreditos = $query->sum('valor');
        
        return view('livewire.customers.conta-corrente', compact(
            'movimentos', 'processos', 'documentos', 'periodoTotalDebitos', 'periodoTotalCreditos'
        ));
    }
}
