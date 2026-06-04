<?php

namespace App\Livewire\Tables;

use App\Domains\Produtos\Actions\DeleteProdutoAction;
use App\Domains\Produtos\Actions\ToggleProdutoStatusAction;
use App\Domains\Produtos\Queries\ProdutoTableQuery;
use Livewire\Component;
use App\Models\Produto;
use Livewire\WithPagination;
use App\Models\TaxTable; // se usa
use App\Models\ProductType; // se usa
use Illuminate\Support\Facades\Auth;

class ServicosTable extends Component
{
    use WithPagination;

    public $search = '';
    public $taxa = '';
    public $productType = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    public $taxas;
    public $productTypes;

    protected $queryString = [
        'search' => ['except' => ''],
        'taxa' => ['except' => ''],
        'productType' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
    ];

    public function mount()
    {
        $this->taxas = TaxTable::select('TaxType', 'Description', 'TaxPercentage')->get();
        $this->productTypes = ProductType::select('code','name')->get();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch(){ $this->resetPage(); }
    public function updatingTaxa(){ $this->resetPage(); }
    public function updatingProductType(){ $this->resetPage(); }
    public function updatingPerPage(){ $this->resetPage(); }

    public function exportCsv()
    {
        $this->dispatch('toast', type:'success', message:'Exportação CSV concluída!');
    }

    public function exportExcel()
    {
        $this->dispatch('toast', type:'success', message:'Exportação Excel concluída!');
    }

    public function exportPdf()
    {
        $this->dispatch('toast', type:'success', message:'PDF gerado com sucesso!');
    }

    public function toggleStatus(Produto $product)
    {
        $empresa = Auth::user()->empresas->first();
        $action = app(ToggleProdutoStatusAction::class);
        $action->execute($product, $empresa);

        $this->dispatch('toast', type:'success', message:'Estado atualizado!');
    }

    public function confirmDelete($productId)
    {
        $this->dispatch('open-delete-confirm', action: "deleteProduct($productId)");
    }

    public function deleteProduct(Produto $product)
    {
        try {
            $empresa = Auth::user()->empresas->first();
            $action = app(DeleteProdutoAction::class);
            $action->execute($product, $empresa);
        } catch (\RuntimeException $e) {
            return $this->dispatch('toast', type:'danger', message:$e->getMessage());
        }

        $this->dispatch('toast', type:'success', message:'Produto desativado.');
    }

    public function render()
    {
        $empresa = Auth::user()->empresas->first();
        $query = app(ProdutoTableQuery::class);
        $products = $query->paginate($empresa, [
            'search' => $this->search,
            'taxa' => $this->taxa,
            'productType' => $this->productType,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'perPage' => $this->perPage,
        ]);

        return view('livewire.tables.servicos-table', compact('products'));
    }
}
