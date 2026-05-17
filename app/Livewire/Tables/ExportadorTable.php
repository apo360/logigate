<?php

namespace App\Livewire\Tables;

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
            Exportador::find($this->exportadorIdToDelete)->delete();
            $this->confirmingDelete = false;
            $this->exportadorIdToDelete = null;
            $this->dispatch('toast', type: 'success', message: 'Exportador excluído com sucesso.');
        }
    }

    public function render()
    {
        // Obtém a empresa do utilizador autenticado
        $empresa = Auth::user()->empresas->first(); // ou use a lógica da empresa actual
        $empresaId = $empresa ? $empresa->id : null;

        $exportadores = Exportador::query()
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('Exportador', 'like', '%'.$this->search.'%')
                    ->orWhere('ExportadorTaxID', 'like', '%'.$this->search.'%')
                    ->orWhere('Endereco', 'like', '%'.$this->search.'%')
                    ->orWhere('Telefone', 'like', '%'.$this->search.'%')
                    ->orWhere('Email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Estatísticas também filtradas pela empresa
        $stats = (object) [
            'total' => Exportador::where('empresa_id', $empresaId)->count(),
            'ativos' => Exportador::where('empresa_id', $empresaId)->count(), // ajuste se tiver campo status
            'com_licenciamentos' => 0,
        ];

        return view('livewire.tables.exportador-table', [
            'exportadores' => $exportadores,
            'stats' => $stats,
        ]);
    }
}