<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Customer;
use App\Models\Processo;
use App\Models\Licenciamento;
use App\Models\ContaCorrente;
use Illuminate\Support\Facades\Auth;

class ClienteTable extends Component
{
    use WithPagination, WithFileUploads;

    // --- FILTROS --- //
    public $search = '';
    public $is_active = '';
    public $tipoCliente = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Parametro de Importação
    public $importFile;
    public $importType = 'excel'; // excel ou csv
    public $showImportModal = false;

    // Informações expandidas
    public $expandedRows = [];

    // Query string para manter estado nos filtros
    protected $queryString = [
        'search' => ['except' => ''],
        'is_active' => ['except' => ''],
        'tipoCliente' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
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

    public function updatingIsActive()
    {
        $this->resetPage();
    }
    
    public function updatingTipoCliente()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    // Obter estatísticas do cliente
    private function getClienteStats($customerId)
    {
        return [
            'processos' => [
                'total' => Processo::where('customer_id', $customerId)->count(),
                'ativos' => Processo::where('customer_id', $customerId)
                    ->whereIn('Estado', ['Aberto', 'Em curso', 'Alfandega'])
                    ->count(),
                'finalizados' => Processo::where('customer_id', $customerId)
                    ->where('Estado', 'Finalizado')
                    ->count(),
            ],
            'licenciamentos' => [
                'total' => Licenciamento::where('cliente_id', $customerId)->count(),
                'txt_gerado' => Licenciamento::where('cliente_id', $customerId)
                    ->where('txt_gerado', 1)
                    ->count(),
                'status_fatura' => Licenciamento::where('cliente_id', $customerId)
                    ->where('status_fatura', 'pendente')
                    ->count(),
            ],
            'conta_corrente' => [
                'saldo_contabilistico' => ContaCorrente::where('cliente_id', $customerId)
                    ->orderBy('data', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->value('saldo_contabilistico') ?? 0,
                'ultimo_movimento' => ContaCorrente::where('cliente_id', $customerId)
                    ->orderBy('data', 'desc')
                    ->first(),
            ]
        ];
    }

    // Importar clientes
    public function importClientes()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,csv,txt|max:10240', // 10MB max
        ]);

        try {
            // Obter empresa do usuário autenticado
            $userEmpresaId = Auth::user()->empresas->first()->id ?? null;
            
            if (!$userEmpresaId) {
                throw new \Exception('Usuário não está associado a uma empresa.');
            }

            // Processar o arquivo
            $filePath = $this->importFile->getRealPath();
            
            if ($this->importType === 'csv' || $this->importFile->getClientOriginalExtension() === 'csv') {
                $this->importFromCSV($filePath, $userEmpresaId);
            } else {
                $this->importFromExcel($filePath, $userEmpresaId);
            }

            $this->reset(['importFile', 'showImportModal']);
            
            $this->dispatch('toast',
                type: 'success',
                message: 'Importação concluída com sucesso!'
            );

        } catch (\Exception $e) {
            $this->dispatch('toast',
                type: 'error',
                message: 'Erro na importação: ' . $e->getMessage()
            );
        }
    }

    private function importFromCSV($filePath, $empresaId)
    {
        $file = fopen($filePath, 'r');
        $header = fgetcsv($file); // Primeira linha como cabeçalho
        
        $importedCount = 0;
        
        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);
            
            // Criar cliente
            $cliente = Customer::firstOrCreate(
                ['CustomerTaxID' => $data['CustomerTaxID'] ?? $data['NIF']],
                [
                    'CompanyName' => $data['CompanyName'] ?? $data['Nome'],
                    'CustomerTaxID' => $data['CustomerTaxID'] ?? $data['NIF'],
                    'Telephone' => $data['Telephone'] ?? $data['Telefone'] ?? null,
                    'Email' => $data['Email'] ?? null,
                    'Address' => $data['Address'] ?? $data['Morada'] ?? null,
                    'City' => $data['City'] ?? $data['Cidade'] ?? null,
                    'Country' => $data['Country'] ?? $data['Pais'] ?? 'Angola',
                    'is_active' => $data['is_active'] === 'Ativo' ? 1 : 0,
                    'CustomerType' => $data['CustomerType'] ?? 'Individual',
                    'created_by' => Auth::id(),
                ]
            );
            
            // Associar à empresa
            $cliente->empresas()->syncWithoutDetaching([$empresaId]);
            
            $importedCount++;
        }
        
        fclose($file);
        
        return $importedCount;
    }

    private function importFromExcel($filePath, $empresaId)
    {
        // Para Excel, você pode usar a biblioteca Laravel Excel
        // Instale: composer require maatwebsite/excel
        // E depois:
        // \Excel::import(new CustomersImport($empresaId), $this->importFile);
        
        // Por enquanto, vamos fazer um placeholder
        throw new \Exception('Importação de Excel requer a biblioteca maatwebsite/excel. Instale com: composer require maatwebsite/excel');
    }

    public function deleteCliente($id)
    {
        try {
            $cliente = Customer::findOrFail($id);
            
            // Verificar se há relacionamentos antes de deletar
            if ($cliente->processos()->exists()) {
                throw new \Exception('Não é possível excluir cliente com processos associados.');
            }
            
            $cliente->delete();
            
            $this->dispatch('toast',
                type: 'success',
                message: 'Cliente excluído com sucesso!'
            );
            
        } catch (\Exception $e) {
            $this->dispatch('toast',
                type: 'error',
                message: 'Erro ao excluir cliente: ' . $e->getMessage()
            );
        }
    }

    // Expandir/contrair linha
    public function toggleRow($customerId)
    {
        if (in_array($customerId, $this->expandedRows)) {
            $this->expandedRows = array_diff($this->expandedRows, [$customerId]);
        } else {
            $this->expandedRows[] = $customerId;
        }
    }


    public function toggleStatus($id)
    {
        try {
            $cliente = Customer::findOrFail($id);
            $cliente->update([
                'is_active' => $cliente->is_active === 1 ? 0 : 1
            ]);
            
            $this->dispatch('toast',
                type: 'success',
                message: 'Status atualizado com sucesso!'
            );
            
        } catch (\Exception $e) {
            $this->dispatch('toast',
                type: 'error',
                message: 'Erro ao atualizar status: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        // Obter ID da empresa do usuário autenticado
        $userEmpresaId = Auth::user()->empresas->first()->id ?? null;
        
        if (!$userEmpresaId) {
            // Se não tiver empresa, mostrar todos os clientes (ou vazio)
            $clientes = Customer::where('id', 0)->paginate($this->perPage);
            return view('livewire.tables.cliente-table', compact('clientes'));
        }

        // Buscar clientes da empresa específica
        $query = Customer::whereHas('empresas', function ($q) use ($userEmpresaId) {
            $q->where('empresas.id', $userEmpresaId);
        })
        ->with('empresas')
        ->select('customers.*');

        // Aplicar filtros
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('CompanyName', 'like', "%{$this->search}%")
                  ->orWhere('CustomerTaxID', 'like', "%{$this->search}%")
                  ->orWhere('Email', 'like', "%{$this->search}%");
            });
        }

        if ($this->is_active) {
            $query->where('is_active', $this->is_active);
        }

        if ($this->tipoCliente) {
            $query->where('CustomerType', $this->tipoCliente);
        }

        // Ordenação
        $query->orderBy($this->sortField, $this->sortDirection);

        $clientes = $query->paginate($this->perPage);

        // Estatísticas
        $stats = [
            'total' => Customer::whereHas('empresas', function ($q) use ($userEmpresaId) {
                $q->where('empresas.id', $userEmpresaId);
            })->count(),
            'ativos' => Customer::whereHas('empresas', function ($q) use ($userEmpresaId) {
                $q->where('empresas.id', $userEmpresaId);
            })->where('is_active', 1)->count(),
            'importadores' => Customer::whereHas('empresas', function ($q) use ($userEmpresaId) {
                $q->where('empresas.id', $userEmpresaId);
            })->where('CustomerType', 'Individual')->orWhere('CustomerType', 'Ambos')->count(),
        ];

        return view('livewire.tables.cliente-table', compact('clientes', 'stats'));
    }
}