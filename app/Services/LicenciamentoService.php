<?php

namespace App\Services;

use App\Models\Licenciamento;
use App\Models\LicenciamentoRascunho;
use App\Models\Porto;
use Illuminate\Support\Facades\DB;

class LicenciamentoService
{
    public function create(array $data, int $empresaId): Licenciamento
    {
        return DB::transaction(function () use ($data, $empresaId) {
            $payload = $this->preparePayload($data, $empresaId);
            $payload['adicoes'] = $payload['adicoes'] ?? 0;

            return Licenciamento::create($payload);
        });
    }

    public function createDraft(array $data, int $empresaId): LicenciamentoRascunho
    {
        return DB::transaction(function () use ($data, $empresaId) {
            $data['empresa_id'] = $empresaId;

            return LicenciamentoRascunho::create($data);
        });
    }

    private function preparePayload(array $data, int $empresaId): array
    {
        $data['empresa_id'] = $empresaId;

        if (! empty($data['porto_origem'])) {
            $porto = Porto::where('sigla', $data['porto_origem'])->first();
            $data['pais_origem'] = $porto?->pais_id;
        }

        return $data;
    }
}