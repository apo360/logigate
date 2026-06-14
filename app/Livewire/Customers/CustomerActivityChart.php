<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use App\Models\Customer;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class CustomerActivityChart extends Component
{
    use AuthorizesRequests;

   public int $customerId;
    public int $year;

    protected Customer $customer;

    public array $labels = [];
    public array $processos = [];
    public array $licenciamentos = [];

    public function mount(int $customerId)
    {
        $this->customerId = $customerId;
        $this->year = now()->year;

        $this->customer = $this->tenantCustomerQuery()->findOrFail($this->customerId);
        $this->authorize('view', $this->customer);

        $this->loadData();
    }

    public function updatedYear()
    {
        $this->loadData();

        $this->dispatch('chart-updated', [
            'labels'          => $this->labels,
            'processos'       => $this->processos,
            'licenciamentos'  => $this->licenciamentos,
        ]);
    }

    protected function loadData(): void
    {
        $meses = collect(range(1, 12));

        $processosMes = $this->customer->processos()
            ->whereYear('created_at', $this->year)
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $licenciamentosMes = $this->customer->licenciamento()
            ->whereYear('created_at', $this->year)
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $this->labels = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];

        $this->processos = $meses->map(fn ($m) => $processosMes[$m] ?? 0)->toArray();
        $this->licenciamentos = $meses->map(fn ($m) => $licenciamentosMes[$m] ?? 0)->toArray();
    }
    
    public function render()
    {
        return view('livewire.customers.customer-activity-chart');
    }

    private function tenantCustomerQuery()
    {
        $empresaId = Auth::user()?->empresas()->value('empresas.id');
        abort_if(!$empresaId, 403);

        $query = Customer::query();

        if (!Schema::hasColumn('customers', 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query->where(function ($tenantQuery) use ($empresaId): void {
            $tenantQuery->where('empresa_id', $empresaId);

            if (Schema::hasTable('customers_empresas')) {
                $tenantQuery->orWhereHas('empresas', fn ($empresaQuery) => $empresaQuery->where('empresas.id', $empresaId));
            }
        });
    }
}
