<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Plano;

final class BillingPlanReview
{
    public function report(): array
    {
        return [
            'mode' => 'review_only', 'sales_blocked' => false,
            'plans' => Plano::withTrashed()->with(['modulos', 'itemplano'])->orderBy('ordem')->get()->map(fn ($plan) => [
                'id' => $plan->id, 'name' => $plan->nome, 'description' => $plan->descricao, 'status' => $plan->status,
                'classification' => $plan->deleted_at ? 'archived' : ($plan->status === 'activo' ? 'commercialized' : 'draft_or_inactive'),
                'prices' => ['monthly' => $plan->preco_mensal, 'trimestral' => $plan->preco_trimestral, 'semestral' => $plan->preco_semestral, 'annual' => $plan->preco_anual],
                'limits' => ['users' => $plan->limite_utilizadores, 'processes' => $plan->limite_processos, 'storage_gb' => $plan->limite_armazenamento_gb],
                'advertised_items' => $plan->itemplano->map(fn ($item) => ['item' => $item->item, 'note' => $item->observacao])->all(),
                'module_ids' => $plan->modulos->pluck('id')->unique()->values()->all(),
                'module_names' => $plan->modulos->pluck('module_name')->unique()->values()->all(),
                'issues' => $plan->modulos->isEmpty() ? ['missing_module_mapping'] : [],
            ])->all(),
            'modules' => Module::orderBy('id')->get(['id', 'parent_id', 'module_name', 'description', 'price'])->toArray(),
        ];
    }
}
