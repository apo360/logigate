<?php

declare(strict_types=1);

namespace App\Livewire\Processo;

use App\Application\Processo\DTOs\ProcessoData;
use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Models\Processo;
use Livewire\Component;

final class ProcessoShow extends Component
{
    public Processo $processo;

    public ProcessoData $data;

    public string $tab = 'informacoes';

    public function mount(Processo $processo, ProcessoRepositoryInterface $repository): void
    {
        $this->processo = $repository->findOrFail((int) $processo->id);
        $this->data = ProcessoData::fromModel($this->processo);
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['informacoes', 'mercadorias', 'documentos', 'faturas'], true)) {
            $this->tab = $tab;
        }
    }

    public function render()
    {
        return view('livewire.processo.processo-show');
    }
}
