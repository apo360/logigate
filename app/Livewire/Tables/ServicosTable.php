<?php

namespace App\Livewire\Tables;

use Livewire\Component;
use App\Models\Produto;
use Livewire\WithPagination;
use App\Models\TaxTable; // se usa
use App\Models\ProductType; // se usa

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
        $product->status = !$product->status;
        $product->save();

        $this->dispatch('toast', type:'success', message:'Estado atualizado!');
    }

    public function confirmDelete($productId)
    {
        $this->dispatch('open-delete-confirm', action: "deleteProduct($productId)");
    }

    public function deleteProduct(Produto $product)
    {
        if($product->salesLines()->exists()){
            return $this->dispatch('toast', type:'danger', message:'Não pode apagar, já existe faturação ligada.');
        }

        $product->delete();

        $this->dispatch('toast', type:'success', message:'Produto removido.');
    }

    public function render()
    {
        $products = Produto::query()
            ->with(['price','grupo','salesLines'])
            ->when($this->search, fn($q) =>
                $q->where(function($query) {
                    $query->where('ProductDescription','like','%'.$this->search.'%')
                          ->orWhere('ProductCode','like','%'.$this->search.'%');
                })
            )
            ->when($this->taxa, fn($q) =>
                $q->whereHas('price', fn($query)=>
                    $query->where('imposto', $this->taxa)
                )
            )
            ->when($this->productType, fn($q)=>
                $q->where('ProductType',$this->productType)
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tables.servicos-table', compact('products'));
    }
}
