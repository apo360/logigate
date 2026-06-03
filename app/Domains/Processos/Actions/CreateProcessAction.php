<?php

namespace App\Domains\Processos\Actions;

use App\Domains\Processos\Data\ProcessFormData;
use App\Models\Empresa;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * LEGADO (congelado): não deve ser usado em novas telas.
 * Use a camada App\Application\Processo (Actions/DTOs) como caminho oficial.
 */
final class CreateProcessAction
{
    public function execute(
        ProcessFormData $data,
        Empresa $empresa,
        User $user,
        ?Processo $processo = null,
    ): Processo {
        try {
            return DB::transaction(function () use ($data, $empresa, $user, $processo): Processo {
                $processo ??= new Processo();

                foreach ($processo->getFillable() as $field) {
                    if (array_key_exists($field, $data->attributes)) {
                        $processo->{$field} = $data->attributes[$field];
                    }
                }

                $processo->empresa_id = $empresa->id;
                $processo->user_id = $user->id;
                $processo->save();

                return $processo;
            });
        } catch (\Throwable $e) {
            report($e);

            throw $e;
        }
    }
}
