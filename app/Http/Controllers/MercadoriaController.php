<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\MercadoriaRequest;
use App\Models\Licenciamento;
use App\Models\Mercadoria;
use App\Models\MercadoriaAgrupada;
use App\Models\PautaAduaneira;
use App\Models\Processo;
use App\Models\Subcategoria;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MercadoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    // MercadoriaController.php
    public function getCodigosAduaneiros($cod_pauta)
    {
        // Busca os códigos aduaneiros que começam com o código da subcategoria
        $codigos = PautaAduaneira::where('codigo', 'like', $cod_pauta . '%')->get();

        // Retorna como JSON para ser usado na resposta AJAX
        return response()->json($codigos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($licenciamento_id = null, $processo_id = null)
    {
        // Inicializa variáveis para controle
        $licenciamento = null; $processo = null; $mercadorias = null;

        $pautaAduaneira = PautaAduaneira::all();

        $sub_categorias = Subcategoria::all();

        // Verifica se o licenciamento_id foi passado
        if (request()->get('licenciamento_id')) 
        { 
            $licenciamento = Licenciamento::findOrFail(request()->get('licenciamento_id'));
            $mercadoriasAgrupadas = MercadoriaAgrupada::with('mercadorias')->where('licenciamento_id',request()->get('licenciamento_id'))->get();

            // Calcular o somatório dos preco_total de todas as mercadorias associadas ao processo
            $somaPrecoTotal = Mercadoria::where('licenciamento_id',request()->get('licenciamento_id'))->sum('preco_total');

            $porcentagem = $licenciamento->fob_total > 0 ? ($somaPrecoTotal / $licenciamento->fob_total) * 100 : 0;

            // Redireciona para o formulário de mercadorias com os dados apropriados
            return view('mercadorias.create_mercadoria', compact('licenciamento', 'mercadoriasAgrupadas', 'pautaAduaneira', 'sub_categorias','somaPrecoTotal', 'porcentagem'));
        }

        // Verifica se o processo_id foi passado
        if (request()->get('processo_id')) { 
            $processo = Processo::find(request()->get('processo_id'));
            $mercadoriasAgrupadas = MercadoriaAgrupada::with('mercadorias')->where('processo_id',request()->get('processo_id'))->get();

            // Calcular o somatório dos preco_total de todas as mercadorias associadas ao processo
            $somaPrecoTotal = Mercadoria::where('Fk_Importacao',request()->get('processo_id'))->sum('preco_total');

            $porcentagem = ($somaPrecoTotal / $processo->fob_total) * 100;
            // Redireciona para o formulário de mercadorias com os dados apropriados
            return view('mercadorias.create_mercadoria_proc', compact('processo', 'mercadoriasAgrupadas', 'pautaAduaneira', 'sub_categorias','somaPrecoTotal', 'porcentagem'));
        }

        // Se nenhum dos IDs foi passado, redireciona para uma página de erro ou lista
        return redirect()->back()->with('error', 'Licenciamento ou Processo não especificado.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MercadoriaRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // Validating the request
            $validatedData = $request->validated();

            // Creating a new mercadoria entry
            $mercadoria = Mercadoria::create($validatedData);

            // Atualizar agrupamento de mercadorias
            MercadoriaAgrupada::StoreAndUpdateAgrupamento($mercadoria);

            // Atualização de licenciamento, caso o mesmo exista
            if ($request->has('licenciamento_id')) {
                $licenciamento = Licenciamento::where('id', $validatedData['licenciamento_id'])->first();
                $licenciamento->save();
            }

            // Atualização de processo, caso o mesmo exista
            if ($request->has('processo_id')) {
                $processo = Processo::where('id', $validatedData['processo_id'])->first();
                $processo->fob_total += $mercadoria->preco_total;

                $processo->save();
            }

            DB::commit();

            // Returning success message or redirecting
            return redirect()->back()->with('success', 'Mercadoria criada com sucesso!');

        } catch (QueryException $e) {
            DB::rollBack();

            return DatabaseErrorHandler::handle($e, $request);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao criar mercadoria: ' . $e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Mercadoria $mercadoria)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mercadoria $mercadoria)
    {
        return response()->json($mercadoria);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request  $request, Mercadoria $mercadoria)
    {
        // Validating the request
        $request->validate([
            'Descricao' => 'required|string|max:255',
            'Quantidade' => 'nullable|integer|min:1',
            'Unidade' => 'nullable|string',
            'Qualificacao' => 'nullable|string',
            'Peso' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'preco_unitario' => 'nullable|numeric|min:0',
            'preco_total' => 'required|numeric|min:0',
        ]);
        // Updating the mercadoria entry
        try {
            DB::beginTransaction();

            // If the preco_total has changed, update the related licenciamento or processo
            if ($request->preco_total != $mercadoria->preco_total) {
                $diferenca = $request->preco_total - $mercadoria->preco_total;

                if ($mercadoria->licenciamento_id) {
                    $licenciamento = Licenciamento::find($mercadoria->licenciamento_id);
                    if ($licenciamento) {
                        $licenciamento->fob_total += $diferenca;
                        $licenciamento->save();
                    }
                }

                if ($mercadoria->processo_id) {
                    $processo = Processo::find($mercadoria->processo_id);
                    if ($processo) {
                        $processo->fob_total += $diferenca;
                        $processo->save();
                    }
                }
            }

            // If the Peso has changed, update the related licenciamento
            if ($request->Peso != $mercadoria->Peso && $mercadoria->licenciamento_id) {
                $diferencaPeso = $request->Peso - $mercadoria->Peso;
                $licenciamento = Licenciamento::find($mercadoria->licenciamento_id);
                if ($licenciamento) {
                    $licenciamento->peso_bruto += $diferencaPeso;
                    $licenciamento->save();
                }
            }

            // Update the mercadoria with new data
            $mercadoria->update($request->all());
            // Atualizar agrupamento de mercadorias
            MercadoriaAgrupada::StoreAndUpdateAgrupamento($mercadoria);

            DB::commit();
            // Returning success message or redirecting
            // return redirect()->back()->with('success', 'Mercadoria atualizada com sucesso!');
            return response()->json(['message' => 'Mercadoria atualizada com sucesso!','mercadoria' => $mercadoria], 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return DatabaseErrorHandler::handle($e, $request);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao atualizar mercadoria: ' . $e->getMessage());
        }

    }

    /**
     * MercadoriaController.php
     * Remove the specified resource from storage.
     */
    public function destroy(Mercadoria $mercadoria)
    {
        try {
            DB::beginTransaction();
            /* $licenciamento = Licenciamento::find($mercadoria->licenciamento_id);
            if ($licenciamento) {
                $licenciamento->fob_total -= $mercadoria->preco_total;
                $licenciamento->peso_bruto -= $mercadoria->Peso;
                $licenciamento->save();
            } */

            $mercadoria->delete();
            MercadoriaAgrupada::RemoveAgrupamento($mercadoria);
            DB::commit();
            return redirect()->back()->withErrors(['success' => 'Mercadoria excluída com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Erro ao excluir a mercadoria. Tente novamente.: ' . $e->getMessage()]);
        }
    }

    public function reagrupar($licenciamentoId)
    {
        try {
            DB::unprepared("CALL AgruparMercadorias(?)", [$licenciamentoId]);
            return redirect()->back()->with('success', 'Mercadorias agrupadas com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao agrupar mercadorias: ' . $e->getMessage());
        }
    }

    public function limparAgrupamentosInativos($licenciamentoId)
    {
        DB::beginTransaction();

        try {
            // Buscar todos os agrupamentos
            $agrupamentos = MercadoriaAgrupada::where('licenciamento_id', $licenciamentoId)->get();

            foreach ($agrupamentos as $agrupamento) {
                // Decodificar os IDs das mercadorias do agrupamento
                $mercadoriasIds = json_decode($agrupamento->mercadorias_ids, true);

                // Verificar se algum dos IDs ainda existe na tabela mercadoria
                $existeMercadoria = Mercadoria::whereIn('id', $mercadoriasIds)->exists();

                if (!$existeMercadoria) {
                    // Deletar o agrupamento se não houver mercadorias associadas
                    $agrupamento->delete();
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Agrupamentos inactivos foram removidos com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao limpar agrupamentos inativos: ' . $e->getMessage());
        }
    }
}
