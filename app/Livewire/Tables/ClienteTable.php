<?php

namespace App\Livewire\Tables;

use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Domains\Customers\Data\ClienteRowData;
use App\Domains\Customers\Queries\ClienteTableQuery;

class ClienteTable extends BaseTable
{
    public string $is_active = '';

    public string $tipoCliente = '';

    protected function query()
    {
        $empresaId = app(CustomerTenantAccessService::class)->currentEmpresaId();

        return app(ClienteTableQuery::class)->build(
            search: $this->search,
            isActive: $this->is_active,
            tipoCliente: $this->tipoCliente,
            empresaId: $empresaId
        );
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'is_active',
            'tipoCliente',
        ]);

        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingIsActive(): void
    {
        $this->resetPage();
    }

    public function updatingTipoCliente(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $clientes = $this->query()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $rows = $clientes->getCollection()
            ->map(fn ($cliente) => new ClienteRowData($cliente));

        $clientes->setCollection($rows);

        return view('livewire.tables.cliente-table', [
            'clientes' => $clientes,
            'summary' => $this->summary(),
        ]);
    }

    protected function view()
    {
        return 'livewire.tables.cliente-table';
    }

    private function summary(): array
    {
        $empresaId = app(CustomerTenantAccessService::class)->currentEmpresaId();

        $baseQuery = app(ClienteTableQuery::class)->build(
            search: '',
            isActive: '',
            tipoCliente: '',
            empresaId: $empresaId
        );

        return [
            'total' => (clone $baseQuery)->count(),
            'activos' => (clone $baseQuery)->where('is_active', true)->count(),
            'inactivos' => (clone $baseQuery)->where('is_active', false)->count(),
            'importadores' => (clone $baseQuery)->where('tipo_cliente', 'importador')->count(),
            'exportadores' => (clone $baseQuery)->whereIn('tipo_cliente', ['exportador', 'ambos'])->count(),
        ];
    }
}