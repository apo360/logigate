<?php

namespace App\Livewire\Customers;

use App\Application\Customer\Queries\CustomerDetailsQuery;
use App\Domains\Customers\Services\CustomerAccountStatementService;
use App\Models\Customer;
use Carbon\Carbon;
use Livewire\Component;

class CustomerShow extends Component
{
    public Customer $customer;

    public int $customerId;

    public array $labels = [];

    public array $atividadeMes = [
        'processos' => [],
        'licenciamentos' => [],
    ];

    public string $activePanel = 'overview';

    public function mount(int|Customer $customer, CustomerDetailsQuery $query): void
    {
        $id = $customer instanceof Customer ? $customer->id : (int) $customer;

        $this->customer = $query->execute($id);
        $this->customerId = $this->customer->id;

        $this->prepareActivityChartData();
    }

    public function openPortalCredentialsModal(): void
    {
        $this->dispatch(
            'open-customer-portal-credentials-modal',
            customerId: $this->customer->id
        );
    }

    public function setPanel(string $panel): void
    {
        abort_unless(in_array($panel, ['overview', 'conta-corrente', 'avencas'], true), 404);

        $this->activePanel = $panel;
    }

    // Activar e desativar User
    public function toggleStatus(){
        // 
    }

    private function prepareActivityChartData(): void
    {
        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo)->startOfMonth());

        $this->labels = $months
            ->map(fn (Carbon $date) => $date->format('M/Y'))
            ->values()
            ->toArray();

        $this->atividadeMes = [
            'processos' => $months
                ->map(function (Carbon $date) {
                    return $this->customer->processos
                        ->filter(fn ($processo) => $processo->created_at?->isSameMonth($date))
                        ->count();
                })
                ->values()
                ->toArray(),

            'licenciamentos' => $months
                ->map(function (Carbon $date) {
                    return $this->customer->licenciamento
                        ->filter(fn ($licenciamento) => $licenciamento->created_at?->isSameMonth($date))
                        ->count();
                })
                ->values()
                ->toArray(),
        ];
    }

    public function render()
    {
        $empresaId = auth()->user()?->empresa_id
            ?? auth()->user()?->empresas()->value('empresas.id');

        $statementService = app(CustomerAccountStatementService::class);
        $ultimoMovimentoContaCorrente = $statementService
            ->movimentosRecentes($this->customer->id, $empresaId ? (int) $empresaId : null, 1)
            ->first();

        return view('livewire.customers.customer-show', [
            'labels' => $this->labels,
            'atividadeMes' => $this->atividadeMes,
            'saldoContaCorrente' => $statementService->saldo($this->customer->id, $empresaId ? (int) $empresaId : null),
            'ultimoMovimentoContaCorrente' => $ultimoMovimentoContaCorrente,
        ]);
    }
}
