<?php

declare(strict_types=1);

namespace App\Livewire\Processo;

use App\Domains\Processo\Repositories\ProcessoRepositoryInterface;
use App\Models\PautaAduaneira;
use App\Models\Processo;
use Livewire\Component;

final class ProcessoShow extends Component
{
    public Processo $processo;

    public $pautaAduaneira = [];

    public array $camposNaoPreenchidos = [];
    public array $camposImportantes = [];

    public function mount(Processo $processo, ProcessoRepositoryInterface $repository): void
    {
        $this->pautaAduaneira = PautaAduaneira::all();

        $this->processo = $processo->load(['cliente', 'exportador', 'estancia', 'mercadorias', 'procLicenFaturas', 'mercadoriasAgrupadas']);

        $this->camposImportantes = [
            'estancia_id' => 'Estância Aduaneira',
            'porto_desembarque_id' => 'Porto de Desembarque',
            'localizacao_mercadoria_id' => 'Localização da Mercadoria',
            'regime_aduaneiro' => 'Regime Aduaneiro',
            'fob_total' => 'Valor FOB',
            'Pais_origem' => 'País de Origem',
        ];

        $this->camposNaoPreenchidos = $repository->verificarCamposImportantes($this->camposImportantes);
    }

    public function render()
    {
        return view('livewire.processo.processo-show');
    }
}
