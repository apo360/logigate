<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Processo\DTOs\AtualizarProcessoDTO;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Domains\Processo\Services\ProcessoLifecycleRules;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;

final readonly class AtualizarProcessoAction
{
    public function __construct(
        private ProcessoRepositoryInterface $processos,
        private ProcessoLifecycleRules $rules,
    )
    {
    }

    public function execute(AtualizarProcessoDTO $dto): Processo
    {
        return DB::transaction(function () use ($dto): Processo {
            Processo::query()->whereKey($dto->id)->lockForUpdate()->firstOrFail();
            $processo = $this->processos->findOrFail($dto->id);
            \Illuminate\Support\Facades\Gate::authorize('update', $processo);

            $attributes = $dto->toArray();
            $this->rules->assertEdicaoComum($processo, $attributes);
            $this->rules->assertPodeTransicionar($processo, $dto->estado);
            if (\Illuminate\Support\Facades\Schema::hasColumn('processos', 'cambio_confirmado')) {
                $rateChanged = array_key_exists('Cambio', $attributes) && $attributes['Cambio'] != $processo->Cambio;
                if ($rateChanged && ! ($attributes['cambio_confirmado'] ?? false)) {
                    $attributes['cambio_confirmado'] = false;
                }
                $candidate = array_replace($processo->getAttributes(), $attributes);
                if ($candidate['cambio_confirmado']) {
                    \Illuminate\Support\Facades\Validator::make($candidate, [
                        'Cambio' => ['required', 'numeric', 'gt:0'],
                        'cambio_origem' => ['required', 'string', 'max:150'],
                        'cambio_data' => ['required', 'date', 'before_or_equal:today'],
                    ])->validate();
                }
                $dto = AtualizarProcessoDTO::fromArray(['id' => $dto->id] + $attributes);
            }
            if (array_intersect(array_keys($attributes), ['fob_total', 'frete', 'seguro', 'Cambio', 'cif', 'ValorAduaneiro']) !== []) {
                $effective = array_replace($processo->getAttributes(), $attributes);
                $values = app(\App\Application\Processo\Support\ProcessoFormSupport::class)->calculatedValues(
                    $effective['fob_total'], $effective['frete'], $effective['seguro'], $effective['Cambio'],
                );
                $attributes = array_merge($attributes, $values);
                $this->rules->assertEdicaoComum($processo, $attributes);
                $dto = AtualizarProcessoDTO::fromArray(['id' => $dto->id] + $attributes);
            }
            $this->rules->assertDataFechoNaoAnterior(
                array_key_exists('DataAbertura', $attributes) ? $dto->dataAbertura : $processo->DataAbertura,
                $processo->DataFecho ? substr((string) $processo->DataFecho, 0, 10) : null,
            );

            return $this->processos->update($dto->id, $dto);
        });
    }
}
