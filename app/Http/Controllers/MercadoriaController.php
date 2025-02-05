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

                // Atualizar valores agregados de FOB e peso
                $licenciamento->fob_total += $mercadoria->preco_total;
                $licenciamento->peso_bruto +=  $mercadoria->Peso;

                $licenciamento->save();
            }

            // Atualização de processo, caso o mesmo exista
            if ($request->has('processo_id')) {
                $processo = Processo::where('id', $validatedData['processo_id'])->first();
                $processo->fob_total += $mercadoria->preco_total;

                $processo->save();
                // 
            }

            DB::commit();

            // Returning success message or redirecting
            return redirect()->back()->with('success', 'Mercadoria criada com sucesso!');

        } catch (QueryException $e) {
            DB::rollBack();

            return DatabaseErrorHandler::handle($e, $request);
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
    
        $mercadoria->update($request->all());

        MercadoriaAgrupada::StoreAndUpdateAgrupamento($mercadoria);

        // Returning success message or redirecting
        return response()->json([
            'message' => 'Mercadoria atualizada com sucesso!',
            'mercadoria' => $mercadoria
        ], 200);
    }

    /**
     * MercadoriaController.php
     * Remove the specified resource from storage.
     */
    public function destroy(Mercadoria $mercadoria)
    {
        try {
            DB::beginTransaction();
            $licenciamento = Licenciamento::find($mercadoria->licenciamento_id);
            if ($licenciamento) {
                $licenciamento->fob_total -= $mercadoria->preco_total;
                $licenciamento->peso_bruto -= $mercadoria->Peso;
                $licenciamento->save();
            }

            $mercadoria->delete();
            MercadoriaAgrupada::RemoveAgrupamento($mercadoria);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Mercadoria excluída com sucesso!', 'mercadoria' => $mercadoria], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => true, 'message' => 'Erro ao excluir a mercadoria. Tente novamente.'], 500);
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

    /*public function reagrupar($licenciamentoId)
    {
        $this->limparAgrupamentosInativos($licenciamentoId);

        DB::beginTransaction();

        try {
            // Buscar todas as mercadorias do licenciamento
            $mercadorias = Mercadoria::where('licenciamento_id', $licenciamentoId)->get();

            foreach ($mercadorias as $mercadoria) {
                // Verificar se a mercadoria já está agrupada corretamente
                $agrupamento = MercadoriaAgrupada::where('codigo_aduaneiro', $mercadoria->codigo_aduaneiro)
                ->where(function($query) use ($mercadoria) {
                    $query->where('licenciamento_id', $mercadoria->licenciamento_id)
                        ->orWhere('processo_id', $mercadoria->processo_id);
                })->first();

                if ($agrupamento) {
                    // Atualizar agrupamento existente se necessário
                    $mercadoriasIds = json_decode($agrupamento->mercadorias_ids, true) ?? [];
                    
                    if (!in_array($mercadoria->id, $mercadoriasIds)) {
                        $agrupamento->quantidade_total += $mercadoria->Quantidade;
                        $agrupamento->peso_total += $mercadoria->Peso;
                        $agrupamento->preco_total += $mercadoria->preco_total;

                        // Adicionar o ID da mercadoria ao JSON
                        $mercadoriasIds[] = $mercadoria->id;
                        $agrupamento->mercadorias_ids = json_encode($mercadoriasIds);

                        $agrupamento->save();
                    }
                } else {
                    // Criar um novo agrupamento para mercadorias não agrupadas
                    MercadoriaAgrupada::create([
                        'codigo_aduaneiro' => $mercadoria->codigo_aduaneiro,
                        'licenciamento_id' => $licenciamentoId,
                        'quantidade_total' => $mercadoria->Quantidade,
                        'peso_total' => $mercadoria->Peso,
                        'preco_total' => $mercadoria->preco_total,
                        'mercadorias_ids' => json_encode([$mercadoria->id]),
                    ]);
                }
            }

            // Passo 3: Calcular o total de `preco_total` das mercadorias agrupadas
            $fobTotal = MercadoriaAgrupada::where('licenciamento_id', $licenciamentoId)
            ->sum('preco_total');

            $pesoTotal = MercadoriaAgrupada::where('licenciamento_id', $licenciamentoId)
            ->sum('peso_total');

            // Passo 4: Atualizar o `fob_total` no licenciamento
            $licenciamento = Licenciamento::findOrFail($licenciamentoId);
            $licenciamento->fob_total = $fobTotal;
            $licenciamento->peso_bruto = $pesoTotal;
            $licenciamento->save();

            DB::commit();

            return redirect()->back()->with('success', 'Reagrupamento concluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao reagrupar: ' . $e->getMessage());
        }
    }*/

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

            return redirect()->back()->with('success', 'Agrupamentos inativos foram removidos com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao limpar agrupamentos inativos: ' . $e->getMessage());
        }
    }

}
