<?php

namespace App\Livewire\Tables;

use App\Domains\Customers\Data\ClienteRowData;
use App\Domains\Customers\Queries\ClienteTableQuery;
use Illuminate\Support\Facades\Auth;

class ClienteTable extends BaseTable
{
    public $is_active = '';
    public $tipoCliente = '';

    protected function query()
    {
        $empresaId = Auth::user()?->empresas->first()?->id;

        return app(ClienteTableQuery::class)
            ->build(
                search: $this->search,
                isActive: $this->is_active,
                tipoCliente: $this->tipoCliente,
                empresaId: $empresaId
            );
    }

    public function render()
    {
        $clientes = $this->query()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $rows = $clientes->getCollection()
            ->map(fn($cliente) => new ClienteRowData($cliente));

        $clientes->setCollection($rows);

        return view('livewire.tables.cliente-table', [
            'clientes' => $clientes
        ]);
    }

    protected function view()
    {
        return 'livewire.tables.cliente-table';
    }
}
