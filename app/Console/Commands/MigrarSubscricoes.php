<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use App\Models\Subscricao;
use App\Models\ActivatedModule;
use App\Models\Plano;

class MigrarSubscricoes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrar-subscricoes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar subscrições antigas para novo sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migração de subscrições...');
        
        // Para cada empresa com activated_modules
        $empresas = Empresa::has('activatedModules')->get();
        
        foreach ($empresas as $empresa) {
            $this->info("Processando empresa: {$empresa->Empresa}");
            
            // Verificar se já tem subscrição no novo sistema
            $subscricaoExistente = $empresa->subscricoes()->first();
            
            if ($subscricaoExistente) {
                $this->info("  ✓ Já possui subscrição no novo sistema");
                continue;
            }
            
            // Criar subscrição básica (plano Teste)
            $planoTeste = Plano::where('codigo', 'teste')->first();
            
            if (!$planoTeste) {
                $this->error("  ✗ Plano teste não encontrado");
                continue;
            }
            
            $subscricao = Subscricao::create([
                'empresa_id' => $empresa->id,
                'plano_id' => $planoTeste->id,
                'module_id' => null, // Para compatibilidade
                'tipo_plano' => 'Teste',
                'modalidade_pagamento' => 'mensal',
                'valor_pago' => 0,
                'data_subscricao' => now(),
                'data_inicio' => now()->subMonth(), // Assumindo que está ativo há 1 mês
                'data_expiracao' => now()->addYear(), // Extender por 1 ano
                'status' => Subscricao::STATUS_ATIVA,
                'created_by' => 1 // Sistema
            ]);
            
            // Atualizar activated_modules com subscricao_id
            ActivatedModule::where('empresa_id', $empresa->id)
                          ->update(['subscricao_id' => $subscricao->id]);
            
            $this->info("  ✓ Subscrição migrada: ID {$subscricao->id}");
        }
        
        $this->info('Migração concluída!');
        return 0;
    }
}
