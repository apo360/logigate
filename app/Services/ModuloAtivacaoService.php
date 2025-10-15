<?php

namespace App\Services;

use App\Models\ActivatedModule;
use App\Models\PlanoModulo;
use Illuminate\Support\Facades\Log;

class ModuloAtivacaoService
{
    public function ativarModulos($empresa_id, $plano_id)
    {
        try {
            // Buscar os módulos do plano
            $modulos = PlanoModulo::where('plano_id', $plano_id)
                ->with('modulo.menus') // Garante que menus estão disponíveis
                ->get()
                ->pluck('modulo');

            foreach ($modulos as $modulo) {
                if (!$modulo) {
                    continue; // ignora módulos nulos
                }

                // Pega o primeiro menu associado, se existir
                $menu_id = optional($modulo->menus->first())->id;

                ActivatedModule::updateOrCreate(
                    [
                        'empresa_id' => $empresa_id,
                        'module_id' => $modulo->id,
                        'menu_id' => $menu_id,
                    ],
                    [
                        'active' => true,
                        'activation_date' => now(),
                    ]
                );
            }
        } catch (\Throwable $th) {
            Log::error('Erro ao ativar módulos.', [
                'error' => $th->getMessage(),
                'empresa_id' => $empresa_id,
                'plano_id' => $plano_id,
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }
}

