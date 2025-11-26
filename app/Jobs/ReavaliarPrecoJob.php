<?php

namespace App\Jobs;

use App\Models\Produto;
use App\Models\ProductPrice;
use App\Models\User;
use App\Notifications\PriceReevaluationResultNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReavaliarPrecoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $produtoId;

    /**
     * Job recebe apenas o ID para evitar serialização pesada.
     */
    public function __construct(int $produtoId)
    {
        $this->produtoId = $produtoId;
    }

    /**
     * Reavaliar automaticamente o preço do produto.
     */
    public function handle(): void
    {
        try {
            $produto = Produto::find($this->produtoId);

            if (!$produto) {
                Log::warning("ReavaliarPrecoJob: Produto não encontrado.", [
                    'produto_id' => $this->produtoId
                ]);
                return;
            }

            // Obter preços
            $precos = ProductPrice::where('product_id', $produto->id)
                ->orderBy('created_at', 'asc')
                ->get();

            // Caso não haja histórico suficiente
            if ($precos->count() < 2) {
                Log::info("ReavaliarPrecoJob: Poucos dados para reavaliação.", [
                    'produto_id' => $produto->id
                ]);
                return;
            }

            $precoAntigo = $precos->first()->price;
            $precoAtual  = $precos->last()->price;

            // Cálculo da variação
            $variacao = (($precoAtual - $precoAntigo) / $precoAntigo) * 100;

            // Classificação da IA
            $recomendacao = $this->classificarReavaliacao($variacao);

            // Log do resultado
            Log::info("Resultado da reavaliação automática", [
                'produto_id'   => $produto->id,
                'variacao_%'   => round($variacao, 2),
                'recomendacao' => $recomendacao
            ]);

            // Notificar gestores com o relatório
            $gestores = User::where('perfil', 'gestor')->get();

            foreach ($gestores as $gestor) {
                $gestor->notify(new PriceReevaluationResultNotification(
                    $produto,
                    $precoAntigo,
                    $precoAtual,
                    $variacao,
                    $recomendacao
                ));
            }

        } catch (\Exception $e) {

            Log::error("Erro no ReavaliarPrecoJob", [
                'produto_id' => $this->produtoId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString()
            ]);

        }
    }



    /**
     * IA: Classificar recomendação com base na variação total do período.
     */
    private function classificarReavaliacao(float $variacao): string
    {
        if ($variacao > 20) {
            return "AUMENTAR preço — alta valorização do produto no período.";
        }

        if ($variacao > 5) {
            return "Manter preço — variação positiva moderada.";
        }

        if ($variacao >= -5) {
            return "Manter preço — mercado estável.";
        }

        if ($variacao >= -15) {
            return "REVER margem — pequena queda no valor percebido.";
        }

        return "REDUZIR preço — grande queda no valor percebido pelo mercado.";
    }
}
