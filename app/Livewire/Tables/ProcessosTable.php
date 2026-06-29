<?php

namespace App\Livewire\Tables;

use App\Application\Processo\Actions\ExcluirProcessoAction;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Domains\Processo\Enums\EstadoProcessoEnum;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProcessosTable extends Component
{
    use AuthorizesRequests;
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
        $this->authorize('viewAny', Processo::class);
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
        $this->authorize('viewAny', Processo::class);

        $this->notifications = app(ProcessoTenantAccessService::class)
            ->scopeForUser(Processo::query(), $this->user())
            ->where('Estado', '!=', EstadoProcessoEnum::FINALIZADO->value)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();
    }

    public function toggleNotifications(): void
    {
        $this->showNotifications = ! $this->showNotifications;
    }

    public function deleteProcesso(int $id): void
    {
        try {
            $processo = app(ProcessoTenantAccessService::class)->findForUserOrFail($this->user(), $id);

            $this->authorize('delete', $processo);

            app(ExcluirProcessoAction::class)->execute((int) $processo->id);
            $this->resetPage();
            $this->loadNotifications();
            $this->dispatch('toast', type: 'success', message: 'Processo eliminado com sucesso.');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $this->authorize('viewAny', Processo::class);

        $query = app(ProcessoTenantAccessService::class)->scopeForUser(
            Processo::query()->with(['cliente', 'tipoDeclaracao', 'paisOrigem', 'procLicenFaturas']),
            $this->user()
        );

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

        $sortFields = [
            'NrProcesso' => 'NrProcesso',
            'TipoProcesso' => 'TipoProcesso',
            'Estado' => 'Estado',
            'ValorAduaneiro' => 'ValorAduaneiro',
            'DataAbertura' => 'DataAbertura',
        ];

        $direction = in_array(strtolower((string) $this->sortDirection), ['asc', 'desc'], true)
            ? strtolower((string) $this->sortDirection)
            : 'desc';

        $query->orderBy($sortFields[$this->sortField] ?? 'DataAbertura', $direction);

        $processos = $query->paginate($this->perPage);

        return view('livewire.tables.processos-table', [
            'processos' => $processos,
        ]);
    }

    private function user(): User
    {
        $user = Auth::user();
        abort_if(! $user, 403, 'Usuário autenticado não encontrado.');

        return $user;
    }
}
