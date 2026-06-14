<?php

namespace App\Domains\Integracoes\Repositories;

use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use Illuminate\Support\Collection;

class EloquentEmpresaIntegracaoRepository implements EmpresaIntegracaoRepositoryInterface
{
    public function listForEmpresa(Empresa $empresa): Collection
    {
        return EmpresaIntegracao::query()
            ->where('empresa_id', $empresa->id)
            ->orderBy('tipo')
            ->orderBy('provedor')
            ->get();
    }

    public function findForEmpresa(
        Empresa $empresa,
        TipoIntegracaoEnum $tipo,
        ProvedorIntegracaoEnum $provedor
    ): ?EmpresaIntegracao {
        return EmpresaIntegracao::query()
            ->where('empresa_id', $empresa->id)
            ->where('tipo', $tipo->value)
            ->where('provedor', $provedor->value)
            ->first();
    }

    public function activeForEmpresa(
        Empresa $empresa,
        TipoIntegracaoEnum $tipo,
        ?ProvedorIntegracaoEnum $provedor = null
    ): ?EmpresaIntegracao {
        return EmpresaIntegracao::query()
            ->where('empresa_id', $empresa->id)
            ->where('tipo', $tipo->value)
            ->where('estado', EstadoIntegracaoEnum::Activo->value)
            ->when($provedor, fn ($query) => $query->where('provedor', $provedor->value))
            ->latest('updated_at')
            ->first();
    }

    public function upsert(Empresa $empresa, array $attributes, array $credentials = []): EmpresaIntegracao
    {
        $tipo = $attributes['tipo'] instanceof TipoIntegracaoEnum ? $attributes['tipo']->value : (string) $attributes['tipo'];
        $provedor = $attributes['provedor'] instanceof ProvedorIntegracaoEnum ? $attributes['provedor']->value : (string) $attributes['provedor'];

        $integration = EmpresaIntegracao::query()->firstOrNew([
            'empresa_id' => $empresa->id,
            'tipo' => $tipo,
            'provedor' => $provedor,
        ]);

        $attributes['tipo'] = $tipo;
        $attributes['provedor'] = $provedor;

        if ($integration->exists) {
            unset($attributes['created_by']);
        }

        $integration->fill($attributes);

        if ($credentials !== []) {
            $integration->setCredentials($credentials);
        }

        $integration->save();

        return $integration->refresh();
    }
}
