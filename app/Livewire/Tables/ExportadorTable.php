<?php

namespace App\Livewire\Tables;

use App\Domains\Exportadores\Actions\DeleteExportadorAction;
use App\Domains\Exportadores\Queries\ExportadorTableQuery;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Exportador;
use Illuminate\Support\Facades\Auth;

class ExportadorTable extends Component
{
    use WithPagination;

    // Filtros
    public $search = '';
    public $perPage = 10;
    public $sortField = 'Exportador';
    public $sortDirection = 'asc';

    // Para o modal de exclusão
    public $confirmingDelete = false;
    public $exportadorIdToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'Exportador'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->exportadorIdToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function deleteExportador()
    {
        if ($this->exportadorIdToDelete) {
            $empresa = Auth::user()->empresas->first();
            $exportador = Exportador::findOrFail($this->exportadorIdToDelete);
            $action = app(DeleteExportadorAction::class);

            $action->execute($exportador, $empresa, Auth::user());

            $this->confirmingDelete = false;
            $this->exportadorIdToDelete = null;
            $this->dispatch('toast', type: 'success', message: 'Exportador removido da empresa com sucesso.');
        }
    }

    public function render()
    {
        // Obtém a empresa do utilizador autenticado
        $empresa = Auth::user()->empresas->first(); // ou use a lógica da empresa actual
        $query = app(ExportadorTableQuery::class);

        $exportadores = $query->paginate($empresa, [
            'search' => $this->search,
            'perPage' => $this->perPage,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ]);

        $stats = $query->stats($empresa);

        return view('livewire.tables.exportador-table', [
            'exportadores' => $exportadores,
            'stats' => $stats,
        ]);
    }
}
