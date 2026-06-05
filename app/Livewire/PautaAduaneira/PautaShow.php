<?php

namespace App\Livewire\PautaAduaneira;

use App\Domains\PautaAduaneira\Repositories\PautaAduaneiraRepositoryInterface;
use Livewire\Component;

class PautaShow extends Component
{
    public int $pautaId;

    public function mount(int $pautaId): void
    {
        $this->pautaId = $pautaId;
    }

    public function render()
    {
        return view('livewire.pauta-aduaneira.pauta-show', [
            'pauta' => app(PautaAduaneiraRepositoryInterface::class)->findOrFail($this->pautaId),
        ]);
    }
}
