<?php

namespace App\Livewire\Customers;

use Livewire\Component;
use App\Models\Customer;
use Illuminate\Support\Collection;

class CustomerActivityChart extends Component
{
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

        $this->customer = Customer::findOrFail($this->customerId);

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
}
