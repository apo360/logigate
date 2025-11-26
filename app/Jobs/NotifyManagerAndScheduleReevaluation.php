<?php

namespace App\Jobs;

use App\Models\Produto;
use App\Models\User;
use App\Notifications\PriceUpdatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyManagerAndScheduleReevaluation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $produto;
    protected $oldPrice;
    protected $newPrice;
    protected $impacto;
    protected $notificar;

    /**
     * Create a new job instance.
     */
    public function __construct(Produto $produto, $oldPrice, $newPrice, $impacto, bool $notificar = false)
    {
        $this->produto   = $produto;
        $this->oldPrice  = $oldPrice;
        $this->newPrice  = $newPrice;
        $this->impacto   = $impacto;
        $this->notificar = $notificar;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            /**
             * 1) Enviar notificação ao gestor (se ativado)
             */
            if ($this->notificar === true) {
                $gestores = User::where('perfil', 'gestor')->get();

                foreach ($gestores as $gestor) {
                    $gestor->notify(new PriceUpdatedNotification(
                        $this->produto,
                        $this->oldPrice,
                        $this->newPrice,
                        $this->impacto
                    ));
                }

                Log::info("Notificação de atualização de preço enviada aos gestores", [
                    'produto_id' => $this->produto->id,
                    'old_price'  => $this->oldPrice,
                    'new_price'  => $this->newPrice
                ]);
            }


            /**
             * 2) Agendar reavaliação automática (30 dias)
             */
            ReavaliarPrecoJob::dispatch($this->produto->id)
                ->delay(now()->addDays(30));

            Log::info("Reavaliação do preço agendada para 30 dias", [
                'produto_id' => $this->produto->id,
                'scheduled_date' => now()->addDays(30)->toDateTimeString()
            ]);

        } catch (\Exception $e) {

            Log::error("Erro ao executar NotifyManagerAndScheduleReevaluation Job", [
                'produto_id' => $this->produto->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
