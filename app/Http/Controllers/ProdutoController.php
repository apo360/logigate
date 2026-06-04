<?php

namespace App\Http\Controllers;

use App\Domains\Produtos\Actions\CreateProdutoAction;
use App\Domains\Produtos\Actions\DeleteProdutoAction;
use App\Domains\Produtos\Actions\ToggleProdutoStatusAction;
use App\Domains\Produtos\Actions\UpdateProdutoAction;
use App\Domains\Produtos\Actions\UpdateProdutoPriceAction;
use App\Domains\Produtos\Data\ProdutoFormData;
use App\Domains\Produtos\Data\ProdutoPriceData;
use App\Domains\Produtos\Queries\ProdutoTableQuery;
use App\Http\Requests\ServicoProdutoRequest;
use App\Http\Requests\ServicoProdutoPriceRequest;
use App\Models\Produto;
use App\Models\ProductGroup;
use App\Models\ProductType;
use App\Models\ProductExemptionReason;
use App\Models\ProductPriceLogs;
use App\Models\TaxTable;
use App\Services\ProdutoService;
use App\Services\ProdutoPriceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ProdutoController extends AuthenticatedController
{
    protected ProdutoService $productService;
    protected ProdutoPriceService $priceService;

    public function __construct(ProdutoService $productService, ProdutoPriceService $priceService)
    {
        parent::__construct();

        $this->productService = $productService;
        $this->priceService = $priceService;
    }

    /**
     * Listagem
     */
    public function index(ProdutoTableQuery $query)
    {
        return view('service.list_service_produto', [
            'products' => $query->paginate($this->empresa),
            'taxas' => TaxTable::orderBy('TaxPercentage', 'desc')->get(),
            'productTypes' => ProductType::all(),
            'productExemptionReasons' => ProductExemptionReason::all(),
            'grupoProduto' => ProductGroup::all()
        ]);
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return view('service.create_service', [
            'productTypes' => ProductType::all(),
            'productExemptionReasons' => ProductExemptionReason::all(),
            'taxas' => TaxTable::orderBy('TaxPercentage', 'desc')->get(),
            'categories' => ProductGroup::all(),
        ]);
    }

    /**
     * Store (com lógica 100% movida para Services + Observers)
     */
    public function store(
        ServicoProdutoRequest $request,
        ServicoProdutoPriceRequest $priceRequest,
        CreateProdutoAction $action
    )
    {
        $action->execute(
            ProdutoFormData::fromArray($request->validated()),
            ProdutoPriceData::fromArray($priceRequest->validated()),
            $this->empresa
        );

        return redirect()->route('produtos.index')->with('status', 'Produto/Serviço Criado com Sucesso');
    }

    /**
     * Show (API)
     */
    public function show($id)
    {
        return view('service.show_service', [
            'produto' => Produto::with(['price', 'grupo', 'tipo', 'empresa'])
                ->where('empresa_id', $this->empresa->id)
                ->findOrFail($id),
            'productTypes' => ProductType::all(),
            'productExemptionReasons' => ProductExemptionReason::all(),
            'categories' => ProductGroup::all(),
            'taxas' => TaxTable::orderBy('TaxPercentage', 'desc')->get(),
            'LogsPrices' => ProductPriceLogs::where('produto_id', $id)->get(),
        ]);
    }

    /**
     * Editar
     */
    public function edit($id)
    {
        return view('service.edit_service', [
            'produto' => Produto::where('empresa_id', $this->empresa->id)->findOrFail($id),
            'productTypes' => ProductType::all(),
            'productExemptionReasons' => ProductExemptionReason::all(),
            'categories' => ProductGroup::all(),
            'taxas' => TaxTable::orderBy('TaxPercentage', 'desc')->get()
        ]);
    }

    /**
     * Update refatorado
     */
    public function update(ServicoProdutoRequest $request, Produto $produto, UpdateProdutoAction $action)
    {
        $action->execute(
            $produto,
            ProdutoFormData::fromArray($request->validated()),
            $this->empresa
        );

        
        return redirect()->back()
            ->with('success', 'Produto/Serviço atualizado com sucesso');
    }

    /**
     * Eliminar Produto
     */
    public function destroy(Produto $produto, DeleteProdutoAction $action)
    {
        $action->execute($produto, $this->empresa);

        return redirect()->back()->with('status', 'Produto/Serviço desativado.');
    }

    /**
     * Export CSV
     */
    public function export()
    {
        return $this->productService->exportCSV();
    }

    /**
     * Import CSV
     */
    public function import()
    {
        return $this->productService->importCSV(request());
    }

    /**
     * Relatórios
     */
    public function report()
    {
        return view('produtos.reports', [
            'topSellingProducts' => $this->productService->getTopSellingProducts()
        ]);
    }

    /**
     * Atualizar estado (ativo/inativo)
     */
    public function updateStatus($id, ToggleProdutoStatusAction $action)
    {
        $produto = Produto::findOrFail($id);
        $action->execute($produto, $this->empresa);

        return redirect()->back()->with('status', 'Estado alterado.');
    }

    /**
     * Atualizar preço (formulário)
     */
    public function showUpdatePriceForm(Produto $produto)
    {
        abort_unless((int) $produto->empresa_id === (int) $this->empresa->id, 403);

        return view('service.update_price', [
            'produto' => $produto,
            'taxas' => TaxTable::orderBy('TaxPercentage', 'desc')->get(),
            'temUsoFiscal' => false, // Ajuste conforme necessário
        ]);
        //return redirect()->back()->with('status', 'Estado alterado.');
    }

    /**
     * Atualizar preço (lógica)
     */    
    public function updatePrice(Request $request, Produto $produto, UpdateProdutoPriceAction $action)
    {
        // Validação
        $request->validate([
            'new_price' => 'required|numeric|min:0.01',
            'motivo'     => 'nullable|string|max:500',
            'notificar'  => 'nullable|in:0,1',
            'observacoes'=> 'nullable|string|max:1000',
        ]);

        // Normaliza o array estruturado para o service
        $data = $request->only(['new_price','motivo','notificar','observacoes']);

        try {
            // Fluxo completo de atualização de preço
            $action->execute($produto, ProdutoPriceData::fromArray($data), $this->empresa);

        } catch (\Exception $e) {

            // Log de erro
            Log::error('Erro ao atualizar preço do produto', [
                'produto_id' => $produto->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Ocorreu um erro ao atualizar o preço do produto.');
        }

        return redirect()
            ->route('produtos.show', $produto->id)
            ->with('status', 'Preço atualizado com sucesso.');
    }

}
