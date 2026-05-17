<?php

namespace App\Domains\Exportador\Actions;

use App\Domains\Exportador\Data\ExportadorFormData;
use App\Models\Exportador;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateExportadorAction
{
    public function execute(
        ExportadorFormData $formData,
        Empresa $empresa,
        User $user,
        ?Exportador $exportador = null) : Exportador{

    try{
        return DB::transaction(function() use ($formData, $empresa, $user, $exportador) {
            // Verifica se o exportador já existe globalmente
            $exportador ??= new Exportador();

            // Pegar os Fillable do modelo Exportador
            foreach ($exportador->getFillable() as $field) {
                if (property_exists($formData, $field)) {
                    $exportador->{$field} = $formData->{$field};
                }
            }

            $exportador->empresa_id = $empresa->id;
            $exportador->user_id = $user->id;
            $exportador->save();

            return $exportador->refresh();
        });
        } catch (\Throwable $e) {
            report($e);
            throw $e;
        }
    }
}