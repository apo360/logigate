<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use Livewire\WithPagination;
use App\Application\Licenciamento\Services\LicenciamentoTenantAccessService;
use App\Models\Licenciamento;
use App\Models\Estancia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class LicenciamentoTable extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    // --- FILTROS ---
    public $search = '';
    public $status = '';
    public $estancia_id = '';
    public $data_inicio = '';
    public $data_fim = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    // --- SELEÇÃO EM MASSA ---
    public $selectedLicenciamentos = [];
    public $selectAll = false;

    // --- QUERY STRING (persiste filtros na URL) ---
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'estancia_id' => ['except' => ''],
        'data_inicio' => ['except' => ''],
        'data_fim' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
    ];

    // --- LISTENERS para eventos (opcional) ---
    protected $listeners = ['refreshTable' => '$refresh'];

    public function mount()
    {
        $this->authorize('viewAny', Licenciamento::class);
    }

    // --- ORDENAÇÃO ---
    public function sortBy($field)
    {
        if (! array_key_exists($field, $this->sortableColumns())) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // --- RESET DE PÁGINA QUANDO FILTROS MUDAM ---
    public function updatingSearch()
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedLicenciamentos = [];
    }

    public function updatingStatus()
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedLicenciamentos = [];
    }

    public function updatingEstanciaId()
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedLicenciamentos = [];
    }

    public function updatingDataInicio()
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedLicenciamentos = [];
    }

    public function updatingDataFim()
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedLicenciamentos = [];
    }

    public function updatingPerPage()
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedLicenciamentos = [];
    }

    public function getStatsProperty()
    {
        $query = $this->getLicenciamentosQuery(); // reutiliza a mesma lógica de filtros
        return (object) [
            'total' => $query->count(),
            'txt_gerado' => (clone $query)->where('licenciamentos.txt_gerado', 1)->count(),
            'pendentes' => (clone $query)->where('status_fatura', 'pendente')->count(),
            'processados' => (clone $query)
                ->where('licenciamentos.txt_gerado', 1)
                ->whereHas('procLicenFaturas', fn ($query) => $query->where('status_fatura', 'paga'))
                ->count(),
        ];
    }

    // --- LIMPAR TODOS OS FILTROS ---
    public function limparFiltros()
    {
        $this->reset(['search', 'status', 'estancia_id', 'data_inicio', 'data_fim']);
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedLicenciamentos = [];
    }

    // --- GERENCIAR SELEÇÃO EM MASSA ---
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Busca todos os IDs da página atual (ou de todos os registos? Vamos usar apenas da página)
            $this->selectedLicenciamentos = $this->getLicenciamentosQuery()
                ->pluck('id')
                ->map(fn($id) => (string) $id) // garantir string para Livewire
                ->toArray();
        } else {
            $this->selectedLicenciamentos = [];
        }
    }

    public function limparSelecao()
    {
        $this->selectedLicenciamentos = [];
        $this->selectAll = false;
    }

    // --- EXPORTAÇÃO DOS SELECIONADOS (chamada pelo botão "Exportar") ---
    public function exportarSelecionados()
    {
        if (empty($this->selectedLicenciamentos)) {
            $this->dispatch('toast', type: 'error', message: 'Nenhum licenciamento selecionado.');
            return;
        }

        // Aqui chama a exportação filtrando pelos IDs
        // Exemplo: (new ExportLicenciamentos($this->selectedLicenciamentos))->download('licenciamentos.xlsx');
        $this->dispatch('toast', type: 'success', message: count($this->selectedLicenciamentos) . ' licenciamento(s) exportado(s).');
    }

    // --- MÉTODO AUXILIAR PARA REUTILIZAR A QUERY ---
    private function getLicenciamentosQuery()
    {
        $query = Licenciamento::query()
            ->with(['cliente', 'procLicenFaturas'])
            ->leftJoin('customers', 'licenciamentos.cliente_id', '=', 'customers.id')
            ->select('licenciamentos.*') // evitar conflito de colunas
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('licenciamentos.descricao', 'like', '%' . $this->search . '%')
                      ->orWhere('customers.CompanyName', 'like', '%' . $this->search . '%')
                      ->orWhere('licenciamentos.referencia_cliente', 'like', '%' . $this->search . '%')
                      ->orWhere('licenciamentos.codigo_licenciamento', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($q): void {
                match ($this->status) {
                    'pendente' => $q->where(function ($query): void {
                        $query->whereNull('licenciamentos.txt_gerado')->orWhere('licenciamentos.txt_gerado', 0);
                    }),
                    'processado' => $q->where('licenciamentos.txt_gerado', 1)
                        ->whereHas('procLicenFaturas', fn ($query) => $query->where('status_fatura', 'paga')),
                    'gerado' => $q->where('licenciamentos.txt_gerado', 1)
                        ->whereDoesntHave('procLicenFaturas', fn ($query) => $query->where('status_fatura', 'paga')),
                    default => null,
                };
            })
            ->when($this->estancia_id, fn($q) => $q->where('licenciamentos.estancia_id', $this->estancia_id))
            ->when($this->data_inicio, fn($q) => $q->whereDate('licenciamentos.created_at', '>=', $this->data_inicio))
            ->when($this->data_fim, fn($q) => $q->whereDate('licenciamentos.created_at', '<=', $this->data_fim))
            ->orderBy($this->sortableColumns()[$this->sortField] ?? 'licenciamentos.created_at', $this->sortDirection);

        return app(LicenciamentoTenantAccessService::class)->scopeForUser($query, Auth::user());
    }

    private function sortableColumns(): array
    {
        return [
            'created_at' => 'licenciamentos.created_at',
            'cliente' => 'customers.CompanyName',
            'descricao' => 'licenciamentos.descricao',
            'peso_bruto' => 'licenciamentos.peso_bruto',
            'porto_origem' => 'licenciamentos.porto_origem',
            'estado_licenciamento' => 'licenciamentos.txt_gerado',
            'cif' => 'licenciamentos.cif',
        ];
    }

    // --- RENDER PRINCIPAL ---
    public function render()
    {
        $licenciamentos = $this->getLicenciamentosQuery()->paginate($this->perPage);

        // Carrega estâncias para o filtro (necessário na view)
        $estancias = Estancia::orderBy('desc_estancia')->get();

        return view('livewire.tables.licenciamento-table', [
            'licenciamentos' => $licenciamentos,
            'estancias' => $estancias,
        ]);
    }
}
