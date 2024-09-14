<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\ServicoProdutoRequest;
use App\Models\ProductExemptionReason;
use App\Models\ProductGroup;
use App\Models\ProductType;
use App\Models\Produto;
use App\Models\TaxTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtém todos os produtos do banco de dados 
        $products = DB::table('Listar_Produtos')->get();
        $productTypes = ProductType::all();
        $productExemptionReasons = ProductExemptionReason::all();
        $taxas = TaxTable::orderBy('TaxPercentage', 'desc')->get();
        $grupoProduto = ProductGroup::all();

        // Retorna a lista de produtos como uma resposta JSON
        return view('service.list_service_produto', compact('products', 'taxas', 'productTypes', 'productExemptionReasons', 'grupoProduto'));
    }

    public function InsertGrupo(Request $request) {
        try {
            ProductGroup::create(
                ['descricao' => $request->input('descricao')]
            );
        } catch (\Exception $e) {
             // Trate a exceção ou retorne um erro apropriado
             return DatabaseErrorHandler::handle($e, $request);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $taxas = TaxTable::orderBy('TaxPercentage', 'desc')->get();
        $categories = ProductGroup::all();
        $productTypes = ProductType::all();
        $productExemptionReasons = ProductExemptionReason::all();
        return view('service.create_service', compact('productTypes', 'productExemptionReasons', 'taxas', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServicoProdutoRequest $request)
    {
        DB::beginTransaction();
        
        try {
            // Cria um novo produto no banco de dados
            Produto::create($request->validated());

            DB::commit();

            // Define a mensagem de sucesso com base no tipo de produto
            $message = ($request['ProductType'] === 'P') ? 'Produto Criado com Sucesso' : 'Serviço Criado com Sucesso';

            return redirect()->back()->with('status', $message);
            
        } catch (\Exception $e) {

            DB::rollBack();

            // Trate a exceção ou retorne um erro apropriado
            return DatabaseErrorHandler::handle($e, $request);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($produto)
    {
        // Obtém um produto específico do banco de dados pelo seu ID
        $product = Produto::findOrFail($produto);

        // Retorna o produto como uma resposta JSON
        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($produtoID)
    {
        $produto = Produto::findOrFail($produtoID);
        $productTypes = ProductType::all();
        $productExemptionReasons = ProductExemptionReason::all();
        return view('service.edit_service', compact('produto', 'productTypes', 'productExemptionReasons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $produto)
    {
        // Valida os dados recebidos
        $validatedData = $request->validate([
            'ProductType' => 'required|in:P,S,O,E,I',
            'ProductCode' => 'required|integer',
            'ProductGroup' => 'required|integer',
            'ProductDescription' => 'required|string|max:100',
            'ProductNumberCode' => 'required|integer',
        ]);

        // Atualiza o produto no banco de dados
        $product = Produto::findOrFail($produto);
        $product->update($validatedData);

        // Retorna o produto atualizado como uma resposta JSON
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        // Obtém um produto específico do banco de dados pelo seu ID
        $product = Produto::findOrFail($produto);

        // Deleta o produto do banco de dados
        $product->delete();

        // Retorna uma resposta vazia com código 204 (No Content)
        return response()->json(null, 204);
    }
}
