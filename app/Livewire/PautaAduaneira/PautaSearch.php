<?php

namespace App\Livewire\PautaAduaneira;

use App\Application\PautaAduaneira\Services\PautaSearchService;
use App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface;
use Livewire\Component;

class PautaSearch extends Component
{
    public string $search = '';

    public ?int $selectedId = null;

    public array $results = [];

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

        $this->selectedId = $pauta->id;
        $this->search = trim($pauta->codigo . ' - ' . $pauta->descricao);
        $this->results = [];

        $this->dispatch(
            'pautaSelecionada',
            pauta_aduaneira_id: $pauta->id,
            codigo: $pauta->codigo,
            descricao: $pauta->descricao,
        );
    }

    public function render()
    {
        return view('livewire.pauta-aduaneira.pauta-search');
    }
}
