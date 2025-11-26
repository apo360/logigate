<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    protected ProdutoService $productService;
    protected ProdutoPriceService $priceService;

    public function __construct(ProdutoService $productService, ProdutoPriceService $priceService)
    {
        $this->productService = $productService;
        $this->priceService = $priceService;
    }

    /**
     * Listagem
     */
    public function index()
    {
        $empresaId = Auth::user()->empresas->first()->id;

        $products = Produto::with(['price', 'grupo'])
            ->where(function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)
                  ->orWhere('empresa_id', 1);
            })
            ->get();

        return view('service.list_service_produto', [
            'products' => $products,
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
    public function store(ServicoProdutoRequest $request, ServicoProdutoPriceRequest $priceRequest)
    {
        DB::transaction(function () use ($request, $priceRequest) {

            // Somente dados do produto
            $produto = $this->productService->createProduct(
                $request->validated(),
                Auth::user()
            );

            // Somente dados de preço
            $this->priceService->createInitialPrice(
                $produto,
                $priceRequest->validated()
            );
        });

        return redirect()->route('produtos.index')->with('status', 'Produto/Serviço Criado com Sucesso');
    }

    /**
     * Show (API)
     */
    public function show($id)
    {
        return view('service.show_service', [
            'produto' => Produto::with(['price', 'grupo', 'tipo', 'empresa'])->findOrFail($id),
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
            'produto' => Produto::findOrFail($id),
            'productTypes' => ProductType::all(),
            'productExemptionReasons' => ProductExemptionReason::all(),
            'categories' => ProductGroup::all(),
            'taxas' => TaxTable::orderBy('TaxPercentage', 'desc')->get()
        ]);
    }

    /**
     * Update refatorado
     */
    public function update(ServicoProdutoRequest $request, Produto $produto)
    {
        DB::transaction(function () use ($produto, $request) {
            // Update do Produto
            $this->productService->updateProduct($produto, $request->validated());

            // Update do Preço + regras através do Observer
            /*$this->priceService->updateProductPrice(
                $produto,
                $request->validated()
            );*/
        });

        
        return redirect()->back()
            ->with('success', 'Produto/Serviço atualizado com sucesso');
    }

    /**
     * Eliminar Produto
     */
    public function destroy(Produto $produto)
    {
        $this->productService->deleteProduct($produto);

        return redirect()->back()->with('status', 'Produto/Serviço apagado.');
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
    public function updateStatus($id)
    {
        $this->productService->toggleStatus($id);

        return redirect()->back()->with('status', 'Estado alterado.');
    }

    /**
     * Atualizar preço (formulário)
     */
    public function showUpdatePriceForm(Produto $produto)
    {
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
    public function updatePrice(Request $request, Produto $produto)
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
            $resultado = $this->priceService->updateProductPrice($produto, $data);

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