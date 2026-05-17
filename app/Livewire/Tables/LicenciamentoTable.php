<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Licenciamento;
use App\Models\Estancia;
use Illuminate\Support\Facades\Auth;
use App\Application\Licenciamento\Actions\Export\ExportLicenciamentosToCsvAction;
use App\Application\Licenciamento\Actions\Export\ExportLicenciamentosToExcelAction;
use App\Domains\Licenciamento\Services\LicenciamentoImportExportService;
use Livewire\WithFileUploads;

class LicenciamentoTable extends Component
{
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

    public $importFile;
    public $showImportModal = false;

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

    // --- ORDENAÇÃO ---
    public function sortBy($field)
    {
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
            'txt_gerado' => (clone $query)->whereNotNull('txt_gerado')->count(),
            'pendentes' => (clone $query)->where('status_fatura', 'pendente')->count(),
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

    // --- EXPORTAÇÃO SIMPLES (sem filtro de seleção) ---
    public function exportCsv()
    {
        $ids = $this->selectedLicenciamentos ?: [];
        return app(ExportLicenciamentosToCsvAction::class)->execute($ids);
    }

    public function exportExcel()
    {
        $ids = $this->selectedLicenciamentos ?: [];
        return app(ExportLicenciamentosToExcelAction::class)->execute($ids);
    }

    public function exportPdf()
    {
        // Implementar PDF se necessário
        $this->dispatch('toast', type: 'info', message: 'PDF em desenvolvimento.');
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,xlsx,xls,txt|max:10240',
        ]);

        try {
            $service = app(LicenciamentoImportExportService::class);
            $licenciamento = $service->import($this->importFile, Auth::user()->empresas->first()->id, auth()->id());
            $this->reset('importFile', 'showImportModal');
            $this->dispatch('toast', type: 'success', message: 'Importação concluída!');
            return redirect()->route('licenciamentos.show', $licenciamento);
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
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

    // --- CONFIRMAÇÃO DE ELIMINAÇÃO (chamado pelo componente td-actions) ---
    public function confirmDelete(int $id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    // --- MÉTODO AUXILIAR PARA REUTILIZAR A QUERY ---
    private function getLicenciamentosQuery()
    {
        return Licenciamento::query()
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
            ->when($this->status, fn($q) => $q->where('licenciamentos.estado_licenciamento', $this->status))
            ->when($this->estancia_id, fn($q) => $q->where('licenciamentos.estancia_id', $this->estancia_id))
            ->when($this->data_inicio, fn($q) => $q->whereDate('licenciamentos.created_at', '>=', $this->data_inicio))
            ->when($this->data_fim, fn($q) => $q->whereDate('licenciamentos.created_at', '<=', $this->data_fim))
            ->orderBy($this->sortField, $this->sortDirection);
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