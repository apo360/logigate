<?php

namespace App\Livewire\PautaAduaneira;

use App\Application\PautaAduaneira\Actions\CalcularTaxasPautaAction;
use App\Application\PautaAduaneira\DTOs\CalculoPautaDTO;
use App\Application\PautaAduaneira\Services\PautaSearchService;
use App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface;
use Livewire\Component;

class SimuladorPauta extends Component
{
    public string $search = '';

    public array $results = [];

    public ?int $pautaId = null;

    public ?string $codigo = null;

    public float|int|string $valorAduaneiro = '';

    public string $regimeTaxa = 'rg';

    public bool $incluirIva = true;

    public bool $incluirIeq = true;

    public ?array $resultado = null;

    public function mount(): void
    {
        $codigo = request()->query('codigo');

        if (! is_string($codigo) || trim($codigo) === '') {
            return;
        }

        $pauta = app(PautaAduaneiraRepositoryInterface::class)->findByCodigo($codigo);

        if (! $pauta) {
            return;
        }

        $this->pautaId = $pauta->id;
        $this->codigo = $pauta->codigo;
        $this->search = trim($pauta->codigo . ' - ' . $pauta->descricao);
    }

    public function updatedSearch(): void
    {
        $term = trim($this->search);

        if (mb_strlen($term) < 2) {
            $this->results = [];
            return;
        }

        $this->results = app(PautaSearchService::class)
            ->search(['q' => $term], 10)
            ->through(fn ($pauta) => [
                'id' => $pauta->id,
                'codigo' => $pauta->codigo,
                'descricao' => $pauta->descricao,
            ])
            ->items();
    }

    public function selectPauta(int $id): void
    {
        $pauta = app(PautaAduaneiraRepositoryInterface::class)->findOrFail($id);

        $this->pautaId = $pauta->id;
        $this->codigo = $pauta->codigo;
        $this->search = trim($pauta->codigo . ' - ' . $pauta->descricao);
        $this->results = [];
        $this->resultado = null;
    }

    public function calcular(): void
    {
        $validated = $this->validate([
            'pautaId' => ['required', 'integer', 'exists:pauta_aduaneira,id'],
            'valorAduaneiro' => ['required', 'numeric', 'min:0'],
            'regimeTaxa' => ['required', 'in:rg,sadc,ua'],
            'incluirIva' => ['boolean'],
            'incluirIeq' => ['boolean'],
        ]);

        $this->resultado = app(CalcularTaxasPautaAction::class)->execute(new CalculoPautaDTO(
            pautaAduaneiraId: (int) $validated['pautaId'],
            valorAduaneiro: (float) $validated['valorAduaneiro'],
            regimeTaxa: $validated['regimeTaxa'],
            incluirIva: (bool) $validated['incluirIva'],
            incluirIeq: (bool) $validated['incluirIeq'],
        ))->toArray();
    }

    public function render()
    {
        return view('livewire.pauta-aduaneira.simulador-pauta');
    }
}
