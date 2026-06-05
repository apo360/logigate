<?php

namespace App\Livewire\PautaAduaneira;

use App\Application\PautaAduaneira\Services\PautaSearchService;
use Livewire\Component;
use Livewire\WithPagination;

class PautaTable extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $pautas = app(PautaSearchService::class)->search([
            'q' => $this->search,
        ], $this->perPage);

        return view('livewire.pauta-aduaneira.pauta-table', [
            'pautas' => $pautas,
        ]);
    }
}
