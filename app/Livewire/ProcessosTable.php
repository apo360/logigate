<?php

namespace App\Livewire;

use App\Models\views\ProcessosView;
use Livewire\Component;
use Livewire\WithPagination;

class ProcessosTable extends Component
{
    use WithPagination;

    public $search = '';
    public $situacao = '';
    public $tipoProcesso = '';
    public $dataCriacao = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'situacao' => ['except' => ''],
        'tipoProcesso' => ['except' => ''],
        'dataCriacao' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSituacao()
    {
        $this->resetPage();
    }

    public function updatingTipoProcesso()
    {
        $this->resetPage();
    }

    public function updatingDataCriacao()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ProcessosView::query();

        if ($this->search) {
            $query->where('NrProcesso', 'like', '%' . $this->search . '%')
                ->orWhere('CompanyName', 'like', '%' . $this->search . '%');
        }

        if ($this->situacao) {
            $query->where('situacao', $this->situacao);
        }

        if ($this->tipoProcesso) {
            $query->where('TipoProcesso', $this->tipoProcesso);
        }

        if ($this->dataCriacao) {
            $query->whereDate('DataAbertura', $this->dataCriacao);
        }

        $processos = $query->paginate(10);

        return view('livewire.processos-table', [
            'processos' => $processos,
        ]);
    }
}
