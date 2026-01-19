<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Licenciamento;

class LicenciamentoTable extends Component
{
    use WithPagination;

    // --- FILTROS ---
    public $search = '';
    public $status = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
    ];

    // Muda sorting
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

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Exportações
    public function exportCsv()
    {
        // tua lógica
        $this->dispatch('toast',
            type: 'success',
            message: 'Exportação CSV concluída!'
        );
    }

    public function exportExcel()
    {
        // tua lógica
        $this->dispatch('toast',
            type: 'success',
            message: 'Exportação Excel concluída!'
        );
    }

    public function exportPdf()
    {
        // tua lógica
        $this->dispatch('toast',
            type: 'success',
            message: 'PDF gerado com sucesso!'
        );
    }

    public function render()
    {
        $licenciamentos = Licenciamento::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('descricao', 'like', '%'.$this->search.'%')
                      ->orWhere('customers.CompanyName', '%'.$this->search.'%')
                      ->orWhere('referencia_cliente', 'like', '%'.$this->search.'%')
                      ->orWhere('codigo_licenciamento', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status, fn($q) =>
                $q->where('estado_licenciamento', $this->status)
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tables.licenciamento-table', [
            'licenciamentos' => $licenciamentos,
        ]);
    }
}
