<?php

namespace App\Domains\Exportadores\Actions;

use App\Domains\Exportadores\Data\ExportadorFormData;
use App\Models\Empresa;
use App\Models\Exportador;
use App\Models\ExportadorEmpresa;
use App\Models\Pais;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateExportadorAction
{
    public function execute(ExportadorFormData $data, Empresa $empresa, User $user): Exportador
    {
        try {
            return DB::transaction(function () use ($data, $empresa, $user): Exportador {
                $payload = $data->toArray();

                $exportador = Exportador::query()
                    ->when(
                        ! empty($payload['ExportadorTaxID']),
                        fn ($query) => $query->where('ExportadorTaxID', $payload['ExportadorTaxID'])
                    )
                    ->where('Exportador', $payload['Exportador'])
                    ->first();

                if (! $exportador) {
                    $exportador = Exportador::create([
                        'Exportador' => $payload['Exportador'],
                        'ExportadorTaxID' => $payload['ExportadorTaxID'] ?? null,
                        'Telefone' => $payload['Telefone'] ?? null,
                        'Email' => $payload['Email'] ?? null,
                        'Pais' => $payload['Pais'] ?: Pais::getByField('pais', 'Angola', 'id'),
                        'Website' => $payload['Website'] ?? null,
                        'user_id' => $user->id,
                    ]);
                }

                ExportadorEmpresa::query()->firstOrCreate(
                    [
                        'exportador_id' => $exportador->id,
                        'empresa_id' => $empresa->id,
                    ],
                    [
                        'status' => 'ATIVO',
                        'data_associacao' => now(),
                    ]
                );

                return $exportador;
            });
        } catch (\Throwable $e) {
            report($e);

            throw $e;
        }
    }
}
