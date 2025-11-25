<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\SalesLine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProdutoService
{
    /**
     * Lista todos os produtos
     */
    public function getAllProducts()
    {
        return Produto::all();
    }

    /**
     * Obtém um produto por ID
     */
    public function getProductById($id)
    {
        return Produto::findOrFail($id);
    }

    /**
     * Cria um novo produto
     *
    * @param array $data
    * @param mixed $user
    * @return Produto
    */
    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['empresa_id'] = Auth::user()->empresas->first()->id;
            $product = Produto::create($data);
            return $product;
        });
    }

    /**
     * Atualiza um produto
     */
    public function updateProduct($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $product = Produto::findOrFail($id);
            $product->update($data);

            return $product;
        });
    }

    /**
     * Elimina um produto
     */
    public function deleteProduct($id)
    {
        return DB::transaction(function () use ($id) {
            $product = Produto::findOrFail($id);
            $product->delete();

            return true;
        });
    }

    /**
     * Ativar / Desativar produto
     */
    public function toggleStatus(Produto $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        return $product;
    }

    /**
     * Exportar todos os produtos para CSV
     */
    public function exportCSV(string $path = 'exports/products.csv')
    {
        $products = Produto::all();

        $handle = fopen(storage_path("app/$path"), 'w');

        // Cabeçalho do CSV
        fputcsv($handle, [
            'ID',
            'Nome',
            'Descrição',
            'SKU',
            'Preço Custo',
            'Unidade Medida',
            'Stock Min',
            'Activo',
            'Criado em',
        ]);

        foreach ($products as $product) {
            fputcsv($handle, [
                $product->id,
                $product->name,
                $product->description,
                $product->sku,
                $product->cost_price,
                $product->unit,
                $product->min_stock,
                $product->is_active,
                $product->created_at,
            ]);
        }

        fclose($handle);

        return "storage/app/$path";
    }

    /**
     * Importar produtos a partir de CSV
     */
    public function importCSV(string $filepath)
    {
        if (!file_exists($filepath)) {
            throw new \Exception("Ficheiro CSV não encontrado: $filepath");
        }

        $handle = fopen($filepath, 'r');

        // Ignorar primeira linha (cabeçalho)
        fgetcsv($handle);

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {

                Produto::updateOrCreate(
                    ['sku' => $row[2]], // Exemplo: evitar duplicação pelo SKU
                    [
                        'name'        => $row[1],
                        'description' => $row[2],
                        'cost_price'  => $row[3],
                        'unit'        => $row[4],
                        'min_stock'   => $row[5],
                        'is_active'   => $row[6] == 1,
                    ]
                );
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        fclose($handle);

        return true;
    }

    /**
     * Top produtos mais vendidos
     */
    public function getTopSellingProducts(int $limit = 10)
    {
        return SalesLine::select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total) as total_amount')
            )
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->with('product')
            ->limit($limit)
            ->get();
    }
}
