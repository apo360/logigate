<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Processo;

class ProcessosTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filtros
    public $search = '';
    public $searchDate = null;
    public $status = '';
    public $sortField = 'DataAbertura';
    public $sortDirection = 'desc';
    public $perPage = 15;

    // Notificações
    public $notifications = [];
    public $showNotifications = true;

    protected $listeners = [
        'deleteProcessoConfirmed' => 'deleteProcesso',
    ];

    protected $queryString = [
        'search'        => ['except' => ''],
        'searchDate'    => ['except' => null],
        'status'        => ['except' => ''],
        'sortField'     => ['except' => 'DataAbertura'],
        'sortDirection' => ['except' => 'desc'],
        'page'          => ['except' => 1],
        'perPage'       => ['except' => 15],
    ];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function updatingSearch()     { $this->resetPage(); }
    public function updatingSearchDate() { $this->resetPage(); }
    public function updatingStatus()     { $this->resetPage(); }
    public function updatingPerPage()    { $this->resetPage(); }
    public function updatingSortField() { $this->resetPage(); }
    public function updatingSortDirection() { $this->resetPage(); }


    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function loadNotifications(): void
    {
        // Ajusta a tua lógica real de "não finalizados"
        $this->notifications = Processo::query()
            ->where('Estado', '!=', 'finalizado')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();
    }

    public function toggleNotifications(): void
    {
        $this->showNotifications = ! $this->showNotifications;
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('confirmDeleteProcesso', id: $id);
    }

    public function deleteProcesso(int $id): void
    {
        $processo = Processo::find($id);

        if ($processo) {
            $processo->delete();
            $this->resetPage();
            $this->loadNotifications();
            $this->dispatch('toast', type: 'success', message: 'Processo eliminado com sucesso.');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Processo não encontrado.');
        }
    }

    public function render()
    {
        $query = Processo::with(['cliente', 'tipoDeclaracao', 'paisOrigem', 'procLicenFaturas']);

        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('NrProcesso', 'like', $search)
                  ->orWhereHas('cliente', function ($q2) use ($search) {
                      $q2->where('CompanyName', 'like', $search)
                         ->orWhere('CustomerTaxID', 'like', $search);
                  });
            });
        }

        if (!empty($this->searchDate)) {
            $query->whereDate('DataAbertura', $this->searchDate);
        }

        if (!empty($this->status)) {
            $query->where('Estado', $this->status);
        }

        if (in_array($this->sortField, ['NrProcesso', 'DataAbertura', 'Situacao'])) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            $query->orderBy('DataAbertura', 'desc');
        }

        $processos = $query->paginate($this->perPage);

        return view('livewire.tables.processos-table', [
            'processos' => $processos,
        ]);
    }
}