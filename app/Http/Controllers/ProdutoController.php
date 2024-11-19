<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\ServicoProdutoRequest;
use App\Models\ProductExemptionReason;
use App\Models\ProductGroup;
use App\Models\ProductPrice;
use App\Models\ProductType;
use App\Models\Produto;
use App\Models\SalesLine;
use App\Models\TaxTable;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtém todos os produtos do banco de dados
        $products = Produto::with(['prices', 'grupo'])->where('empresa_id', Auth::user()->empresas->first()->id)->get();
        $productTypes = ProductType::all();
        $productExemptionReasons = ProductExemptionReason::all();
        $taxas = TaxTable::orderBy('TaxPercentage', 'desc')->get();
        $grupoProduto = ProductGroup::all();

        // Retorna a lista de produtos como uma resposta JSON
        return view('service.list_service_produto', compact('products', 'taxas', 'productTypes', 'productExemptionReasons', 'grupoProduto'));
    }

    public function InsertGrupo(Request $request) {
        // Valida o campo da categoria
        $validatedData = $request->validate(['newCategoryName' => 'required|string|max:255']);
        // Bloco try-catch
        try {
            $category = ProductGroup::create(['descricao' => $validatedData['newCategoryName'],] );
            // Retorna a categoria recém-criada como JSON
            return response()->json([
                'message' => 'Categoria adicionado com Sucesso',
                'categoria_id' => $category->id,
                'categoria_desc' => $category->descricao,
            ], 200);
            return response()->json(['success' => true, 'category' => $category]);
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
        // Validação dos dados
        $validatedData = $request->validated();

        // Se for um serviço, remover alguns campos
        if ($validatedData['ProductType'] === 'S') {
            $validatedData['ProductNumberCode'] = $validatedData['ProductCode']; // Não precisa de código de barras
            $validatedData['unidade'] = 'UN'; // Não precisa de unidade
            $validatedData['imagem'] = null; // Não precisa de imagem
            $validatedData['preco_custo'] = 0; // Custo pode ser zero para serviços
        }
        
        try {
            // Início da transação de banco de dados
            DB::beginTransaction();

            // Obtém o usuário autenticado
            $user = Auth::user();
            $validatedData['empresa_id'] = $user->empresas->first()->id;

            // Cria o produto no banco de dados
            $produto = Produto::create($validatedData);

            // Upload da imagem, se houver
            if ($request->hasFile('imagem')) {
                $image = $request->file('imagem');
                $imagePath = $image->store('produtos', 'public'); // Armazena no diretório público

                // Associa a imagem ao produto
                $produto->imagem()->create(['imagem_path' => $imagePath]);
            }

            // Manipular campo `dedutivel_iva` se não estiver vazio
            if (!empty($validatedData['dedutivel_iva'])) {
                $validatedData['dedutivel_iva'] = floatval($validatedData['dedutivel_iva']);
            }

            // Relacionar o `produto` com a tabela de preços
            $validatedData['fk_product'] = $produto->id;

            // Recupera os dados da taxa de IVA
            
            $tax = TaxTable::findOrFail($validatedData['taxa_iva']);
            $validatedData['taxID'] = $tax->TaxType;
            $validatedData['imposto'] = $tax->TaxPercentage;
            
            // Cálculo do montante do imposto
            $taxAmount = $validatedData['venda_sem_iva'] * ($tax->TaxPercentage / 100);

            // Adicionar o valor calculado aos dados validados
            $validatedData['taxAmount'] = $taxAmount;

            // Criação dos preços relacionados ao produto
            ProductPrice::create($validatedData);

            // Commit da transação
            DB::commit();

            // Mensagem de sucesso com base no tipo de produto
            $message = ($validatedData['ProductType'] === 'P') ? 'Produto Criado com Sucesso' : 'Serviço Criado com Sucesso';

            return redirect()->route('produtos.index')->with('status', $message);
            
        } catch (\Exception $e) {
            // Reverter a transação em caso de erro
            DB::rollBack();

            // Logar o erro para depuração futura
            Log::error('Erro ao criar produto/serviço: ' . $e->getMessage());

            // Retorna um tratamento adequado para o erro
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
    /**
 * Update the specified resource in storage.
 */
    public function update(ServicoProdutoRequest $request, Produto $produto)
    {
        // Validação
        $validatedData = $request->validated();

        // Se for um serviço, remover alguns campos
        if ($request->input('ProductType') === 'S') {
            $validatedData['ProductNumberCode'] = null; // Não precisa de código de barras
            $validatedData['unidade'] = null; // Não precisa de unidade
            $validatedData['imagem'] = null; // Não precisa de imagem
            $validatedData['custo'] = 0;
        }

        try {
            // Início de uma transação de banco de dados
            DB::beginTransaction();

            // Atualizar dados do produto
            $produto->update($validatedData);

            // Upload de imagem (caso exista)
            if ($request->hasFile('imagem')) {
                // Deleta a imagem antiga se existir
                if ($produto->imagem) {
                    Storage::disk('public')->delete($produto->imagem->path);
                }

                // Armazena a nova imagem
                $image = $request->file('imagem');
                $imagePath = $image->store('produtos', 'public');

                // Atualiza ou cria uma nova associação de imagem
                $produto->imagem()->updateOrCreate(
                    ['produto_id' => $produto->id],
                    ['path' => $imagePath]
                );
            }

            // Verificar se o campo `dedutivel_iva` não está vazio e converter para decimal
            if (!empty($validatedData['dedutivel_iva'])) {
                $validatedData['dedutivel_iva'] = floatval($validatedData['dedutivel_iva']);
            }

            // Atualizar informações de preço
            $validatedData['fk_product'] = $produto->id;
            $tax = TaxTable::findOrFail($validatedData['taxa_iva']);
            $validatedData['taxID'] = $tax->TaxType;
            $validatedData['imposto'] = $tax->TaxPercentage;

            // Atualizar os preços
            ProductPrice::updateOrCreate(
                ['fk_product' => $produto->id],
                $request->only(['preco_custo', 'preco_venda', 'margem_lucro', 'preco_sem_iva'])
            );

            // Commit na transação de banco de dados
            DB::commit();

            // Define a mensagem de sucesso
            $message = ($request['ProductType'] === 'P') ? 'Produto Atualizado com Sucesso' : 'Serviço Atualizado com Sucesso';

            return redirect()->back()->with('status', $message);

        } catch (QueryException $e) {
            DB::rollBack();

            // Trate a exceção ou retorne um erro apropriado
            return DatabaseErrorHandler::handle($e, $request);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    
    public function destroy(Produto $produto)
    {
        try {
            // Verificar se o produto está associado a uma linha de fatura em `sales_line`
            $salesLines = SalesLine::where('productID', $produto->id)->exists();

            if ($salesLines) {
                // Caso o produto esteja relacionado a faturas, impedir a exclusão
                return redirect()->back()->with('error', 'Este produto/serviço não pode ser excluído, pois está associado a faturas.');
            }

            // Início de uma transação de banco de dados
            DB::beginTransaction();

            // Deletar a imagem associada (se houver)
            if ($produto->imagem) {
                Storage::disk('public')->delete($produto->imagem->path);
                $produto->imagem->delete();
            }

            // Deletar os preços relacionados
            ProductPrice::where('fk_product', $produto->id)->delete();

            // Deletar o produto
            $produto->delete();

            // Commit na transação de banco de dados
            DB::commit();

            return redirect()->back()->with('status', 'Produto/Serviço excluído com sucesso.');

        } catch (QueryException $e) {
            DB::rollBack();

            // Trate a exceção ou retorne um erro apropriado
            // return DatabaseErrorHandler::handle($e, $produto);
        }
    }

    /**
     * Metodo para exportar produtos
     */

    public function export()
    {
        // Obtém o usuário autenticado
        $user = Auth::user();
        $produtos = Produto::where('empresa_id', $user->empresas->first()->id);
        $csv = fopen('php://output', 'w');
        
        // Cabeçalhos do ficheiro
        fputcsv($csv, ['ID', 'Código', 'Descrição', 'Preço Venda']);
        
        // Adicionar os dados dos produtos
        foreach ($produtos as $produto) {
            fputcsv($csv, [$produto->id, $produto->ProductCode, $produto->ProductDescription, $produto->preco_venda]);
        }

        fclose($csv);

        return response()->streamDownload(function() use ($csv) {
            echo $csv;
        }, 'produtos.csv');
    }

    /**
     * Metodo para importar os produtos
     */
    public function import(Request $request)
    {
        $user = Auth::user();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data = array_map('str_getcsv', file($file));
            
            // Loop para adicionar os produtos no banco
            foreach ($data as $row) {
                Produto::create([
                    'ProductCode' => $row[0],
                    'ProductDescription' => $row[1],
                    'preco_venda' => $row[2],
                    'empresa_id' => $user->empresas->first()->id,
                    // Outros campos conforme a estrutura do CSV
                ]);
            }

            return redirect()->back()->with('status', 'Produtos importados com sucesso.');
        }

        return redirect()->back()->with('error', 'Nenhum arquivo enviado.');
    }

    /**
     * Método de atualização em massa
     */
    //
    public function bulkUpdate(Request $request)
    {
        $productIds = $request->input('product_ids');
        $newPrice = $request->input('preco_venda');
        
        Produto::whereIn('id', $productIds)->update(['preco_venda' => $newPrice]);

        return redirect()->back()->with('status', 'Produtos atualizados com sucesso.');
    }

    /**
     * Método de Ajuste de Stock
     */
    public function adjustStock(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $stockAdjustment = $request->input('stock_adjustment'); // Quantidade a ser ajustada

        // Atualiza o estoque
        $produto->stock += $stockAdjustment;
        $produto->save();

        return redirect()->back()->with('status', 'Estoque ajustado com sucesso.');
    }

    /**
     * Método para Arquivar Produtos
     */
    public function archive($id)
    {
        $produto = Produto::findOrFail($id);

        $produto->archived = true; // Supondo que haja uma coluna 'archived'
        $produto->save();

        return redirect()->back()->with('status', 'Produto arquivado com sucesso.');
    }
 /**
  * Método de relatórios de produtos
  */
  public function report()
  {
      $topSellingProducts = Produto::withCount('sales')
          ->orderBy('sales_count', 'desc')
          ->take(10)
          ->get();
  
      return view('produtos.reports', compact('topSellingProducts'));
  }
  
  /**
 * visão rápida do produto, sem precisar carregar uma página inteira. Para dashboards
 */
public function quickView($id)
{
    $produto = Produto::findOrFail($id);
    return response()->json($produto);
}

/**
 * Permite alterar o status do produto, de "ativo" para "inativo" ou "em promoção".
 */
public function updateStatus(Request $request, $id)
{
    $produto = Produto::findOrFail($id);
    $produto->status = $request->input('status');
    $produto->save();

    return redirect()->back()->with('status', 'Status do produto atualizado.');
}

}


