<?php

namespace App\Livewire\Customers;

use App\Application\Customer\Actions\ListarExtratoClienteAction;
use App\Application\Customer\Actions\RegistrarMovimentoContaCorrenteAction;
use App\Application\Customer\DTOs\ContaCorrenteMovimentoDTO;
use App\Domains\Customers\Services\CustomerAccountStatementService;
use App\Models\Customer;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ContaCorrente extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public int $customerId;

    public Customer $customer;

    public int $empresaId;

    public string $search = '';

    public string $tipo = '';

    public string $data_inicio = '';

    public string $data_fim = '';

    public int $perPage = 10;

    public float $saldoAtual = 0.0;

    public float $totalCreditos = 0.0;

    public float $totalDebitos = 0.0;

    public bool $showStructuredNotice = true;

    public bool $showCreateModal = false;

    public bool $canRegisterMovimento = false;

    public array $form = [
        'tipo' => 'debito',
        'valor' => '',
        'descricao' => '',
        'referencia' => '',
        'data_movimento' => '',
        'observacoes' => '',
    ];

    protected function rules(): array
    {
        return [
            'form.tipo' => 'required|in:debito,credito',
            'form.valor' => 'required|numeric|min:0.01',
            'form.descricao' => 'nullable|string|max:255',
            'form.referencia' => 'nullable|string|max:100',
            'form.data_movimento' => 'required|date',
            'form.observacoes' => 'nullable|string|max:1000',
        ];
    }

    public function mount(int|Customer|null $customer = null, ?int $customerId = null): void
    {
        $this->customerId = $customer instanceof Customer ? (int) $customer->id : (int) ($customerId ?? $customer);
        $this->empresaId = $this->currentEmpresaId();

        $this->customer = $this->tenantCustomerQuery()
            ->findOrFail($this->customerId);

        $this->authorize('view', $this->customer);

        $this->form['data_movimento'] = now()->toDateString();
        $this->canRegisterMovimento = Schema::hasColumn('conta_correntes', 'empresa_id');

        $this->calculateSaldo();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTipo(): void
    {
        $this->resetPage();
    }

    public function updatedDataInicio(): void
    {
        $this->resetPage();
    }

    public function updatedDataFim(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'tipo', 'data_inicio', 'data_fim']);
        $this->resetPage();
    }

    #[On('conta-corrente-atualizada')]
    public function refreshFromAvenca(): void
    {
        $this->calculateSaldo();
        $this->resetPage();
    }

    public function calculateSaldo(): void
    {
        $totais = app(CustomerAccountStatementService::class)->totais($this->customerId, $this->empresaId);

        $this->totalCreditos = $totais['creditos'];
        $this->totalDebitos = $totais['debitos'];
        $this->saldoAtual = $totais['saldo'];
    }

    public function openCreateModal(): void
    {
        abort_unless($this->canRegisterMovimento, 409, 'A Conta Corrente ainda não está pronta para criar movimentos com segurança tenant.');

        $this->resetValidation();
        $this->form = [
            'tipo' => 'debito',
            'valor' => '',
            'descricao' => '',
            'referencia' => '',
            'data_movimento' => now()->toDateString(),
            'observacoes' => '',
        ];
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function registrarMovimento(RegistrarMovimentoContaCorrenteAction $action): void
    {
        abort_unless($this->canRegisterMovimento, 409, 'A Conta Corrente ainda não está pronta para criar movimentos com segurança tenant.');

        $this->authorize('view', $this->customer);
        $this->authorize('create', [\App\Models\ContaCorrente::class, $this->customer]);
        $validated = $this->validate()['form'];

        $action->execute(ContaCorrenteMovimentoDTO::fromArray([
            ...$validated,
            'empresa_id' => $this->empresaId,
            'customer_id' => $this->customerId,
            'created_by' => Auth::id(),
            'origem_tipo' => 'manual',
        ]));

        $this->closeCreateModal();
        $this->calculateSaldo();
        $this->resetPage();
        session()->flash('success', 'Movimento registado com sucesso.');
    }

    public function render()
    {
        return view('livewire.customers.conta-corrente', [
            'movimentos' => app(ListarExtratoClienteAction::class)->execute(
                customerId: $this->customerId,
                empresaId: $this->empresaId,
                tipo: $this->tipo,
                dataInicio: $this->data_inicio,
                dataFim: $this->data_fim,
                search: $this->search,
                perPage: $this->perPage,
            ),
        ]);
    }

    private function tenantCustomerQuery()
    {
        $query = Customer::query();

        if (!Schema::hasColumn('customers', 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query->where(function ($tenantQuery): void {
            $tenantQuery->where('empresa_id', $this->empresaId);

            if (Schema::hasTable('customers_empresas')) {
                $tenantQuery->orWhereHas('empresas', fn ($empresaQuery) => $empresaQuery->where('empresas.id', $this->empresaId));
            }
        });
    }

    private function currentEmpresaId(): int
    {
        $empresaId = Auth::user()?->empresa_id
            ?? Auth::user()?->empresas()->value('empresas.id');

        abort_if(!$empresaId, 403);

        return (int) $empresaId;
    }
}
