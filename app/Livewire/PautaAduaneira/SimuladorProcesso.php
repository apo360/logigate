<?php

namespace App\Livewire\PautaAduaneira;

use App\Application\PautaAduaneira\Actions\CalcularTributacaoProcessoAction;
use App\Application\Processo\Services\ProcessoCustoService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SimuladorProcesso extends Component
{
    #[Locked]
    public int $processoId;

    public string $mensagem = '';

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
        $processo = app(ProcessoCustoService::class)->processo($this->processoId, 'simulate');
        $processo->load('mercadorias.pautaAduaneira', 'tipoDeclaracao');
        abort_unless(strtoupper((string) $processo->tipoDeclaracao?->abrev) === 'IM' && ! $processo->isExportacaoCrude(), 422, 'Simulador de importação não aplicável a esta operação.');
        $this->regimesPorMercadoria = array_filter($this->regimesPorMercadoria, fn ($value) => $value !== '' && $value !== null);
        Validator::make(['regime' => $this->regimeTaxa, 'itens' => $this->regimesPorMercadoria], ['regime' => ['required', Rule::in(['rg', 'sadc', 'ua'])], 'itens.*' => Rule::in(['rg', 'sadc', 'ua'])])->validate();
        foreach (array_keys($this->regimesPorMercadoria) as $key) {
            abort_unless(in_array((int) $key, $processo->mercadorias->modelKeys(), true), 422);
        }

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

    public function guardarVersao(): void
    {
        $this->calcular();
        $version = app(ProcessoCustoService::class)->guardarSimulacao($this->processoId, [
            'regimeTaxa' => $this->regimeTaxa, 'incluirIva' => $this->incluirIva, 'incluirIeq' => $this->incluirIeq, 'regimesPorMercadoria' => $this->regimesPorMercadoria,
        ]);
        $this->mensagem = 'Versão '.$version->versao.' guardada'.($version->completa ? '.' : ' como parcial. Confira os dados e as pautas antes de a usar como estimativa.');
    }

    public function render()
    {
        return view('livewire.pauta-aduaneira.simulador-processo');
    }
}
