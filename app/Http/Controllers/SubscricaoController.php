<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscricao;
use App\Models\ActivatedModule;
use App\Models\Empresa;
use App\Models\Plano;
use App\Models\PlanoModulo;
use App\Services\ModuloAtivacaoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscricaoController extends Controller
{
    // 
    public function index()
    {
        $subscricao = Subscricao::all();
        return view('subscricao.index', compact('subscricao'));
    }

    /**
     * Processar uma nova Subscrição.
     */
    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'plano_id' => 'required|exists:planos,id',
            'modalidade_pagamento' => 'required|in:Mensal,Trimestral,Semestral,Anual',
        ]);

        $plano = Plano::with('modulos')->findOrFail($request->plano_id);

        DB::beginTransaction();

        try {
            // Calcular a duração da subscrição
            $duracao = match ($request->modalidade_pagamento) {
                'Mensal' => 1,
                'Trimestral' => 3,
                'Semestral' => 6,
                'Anual' => 12,
                default => 1
            };

            // Calcular preço do plano (exemplo simples)
            $valorPago = $plano->preco_mensal * $duracao;

            // Registar a subscrição com o plano subscrito
            Subscricao::create([
                'empresa_id' => $request->empresa_id,
                'plano_id' => $request->plano_id,
                'data_subscricao' => Carbon::now(),
                'data_expiracao' => Carbon::now()->addMonths($duracao),
                'tipo_plano' => $plano->nome,
                'modalidade_pagamento' => $request->modalidade_pagamento,
                'valor_pago' => $valorPago,
                'status' => 'ATIVA',
            ]);
            // Chama o Serviço de Activação de Módulos
            app(ModuloAtivacaoService::class)->ativarModulos($request->empresa_id, $plano->id);
            
            // Confirmar a transação
            DB::commit();

            return response()->json([
                'message' => 'Subscrição concluída com sucesso!',
                'empresa_id' => $request->empresa_id,
                'plano' => $plano->nome,
                'expira_em' => Carbon::now()->addMonths($duracao)->toDateString(),
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Erro ao processar subscrição: ' . $th->getMessage());
            
            return response()->json([
                'error' => 'Erro ao processar subscrição',
                'detalhes' => $th->getMessage(),
            ], 500);
            
        }
    }

    /**
     * Ativar menus e submenus de um módulo
     */

    /**
     * Desativar subscrições expiradas (CRON JOB diário)
     */
    public function checkExpiradas()
    {
        $expiradas = Subscricao::where('status', 'ATIVA')
            ->whereDate('data_fim', '<', Carbon::now())
            ->get();

        foreach ($expiradas as $sub) {
            $sub->update(['status' => 'EXPIRADA']);

            ActivatedModule::where('empresa_id', $sub->empresa_id)
                ->where('module_id', $sub->module_id)
                ->update(['active' => false, 'deactivated_at' => now()]);
        }

        return response()->json([
            'message' => 'Subscrições expiradas desativadas com sucesso.',
            'count' => $expiradas->count(),
        ]);
    }
    
}
