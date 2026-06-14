<?php

declare(strict_types=1);

namespace App\Livewire\Processo;

use App\Models\PautaAduaneira;
use App\Models\Processo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

final class ProcessoShow extends Component
{
    use AuthorizesRequests;

    public Processo $processo;

    public $pautaAduaneira = [];

    public array $camposNaoPreenchidos = [];
    public array $camposImportantes = [];

    public function mount(Processo $processo): void
    {
        $this->authorize('view', $processo);

        $this->processo = $processo->loadMissing([
            'cliente',
            'exportador',
            'estancia',
            'tipoDeclaracao',
            'paisOrigem',
            'paisDestino',
            'nacionalidadeNavio',
            'mercadorias',
            'procLicenFaturas',
            'mercadoriasAgrupadas',
            'emolumentoTarifa',
            'portoDesembarque',
            'localizacaoMercadoria',
        ]);

        $codigosPautais = $this->processo->mercadorias
            ->pluck('codigo_aduaneiro')
            ->filter()
            ->unique()
            ->values();

        $this->pautaAduaneira = $codigosPautais->isEmpty()
            ? collect()
            : PautaAduaneira::query()->whereIn('codigo_sem_pontos', $codigosPautais)->get();

        $this->camposImportantes = [
            'estancia_id' => 'Estância Aduaneira',
            'porto_desembarque_id' => 'Porto de Desembarque',
            'localizacao_mercadoria_id' => 'Localização da Mercadoria',
            'regime_aduaneiro' => 'Regime Aduaneiro',
            'fob_total' => 'Valor FOB',
            'Pais_origem' => 'País de Origem',
        ];

        $this->camposNaoPreenchidos = collect($this->camposImportantes)
            ->filter(fn (string $label, string $campo): bool => blank($this->processo->{$campo}))
            ->all();
    }

    public function render()
    {
        return view('livewire.processo.processo-show');
    }
}
