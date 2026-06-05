<?php

namespace App\Livewire\PautaAduaneira;

use App\Application\PautaAduaneira\Actions\CalcularTributacaoProcessoAction;
use App\Models\Processo;
use Livewire\Component;

class SimuladorProcesso extends Component
{
    public int $processoId;

    public string $regimeTaxa = 'rg';

    public bool $incluirIva = true;

    public bool $incluirIeq = true;

    public array $regimesPorMercadoria = [];

    public array $resultado = [
        'items' => [],
        'totais' => [],
        'alertas' => [],
    ];

    public function mount(int $processoId): void
    {
        $this->processoId = $processoId;
        $this->calcular();
    }

    public function updated($field): void
    {
        if (str_starts_with((string) $field, 'regimesPorMercadoria') || in_array($field, ['regimeTaxa', 'incluirIva', 'incluirIeq'], true)) {
            $this->calcular();
        }
    }

    public function calcular(): void
    {
        $processo = Processo::with('mercadorias.pautaAduaneira')->findOrFail($this->processoId);

        $this->resultado = app(CalcularTributacaoProcessoAction::class)
            ->execute(
                processo: $processo,
                regimeTaxa: $this->regimeTaxa,
                incluirIva: $this->incluirIva,
                incluirIeq: $this->incluirIeq,
                regimesPorMercadoria: $this->regimesPorMercadoria,
            )
            ->toArray();
    }

    public function render()
    {
        return view('livewire.pauta-aduaneira.simulador-processo');
    }
}
