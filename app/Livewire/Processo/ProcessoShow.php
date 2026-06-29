<?php

namespace App\Livewire\Processo;

use App\Application\Processo\Actions\EmitirNotaDespesaProcessoAction;
use App\Application\Processo\Actions\GerarExtratoMercadoriaProcessoAction;
use App\Application\Processo\Actions\GerarTxtProcessoAction;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Models\PautaAduaneira;
use App\Models\Processo;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
        $user = Auth::user();
        abort_if(! $user, 403, 'Usuário autenticado não encontrado.');
        abort_unless(app(ProcessoTenantAccessService::class)->canAccess($user, $processo), 404);

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
            : PautaAduaneira::query()->whereIn('codigo', $codigosPautais)->get();

        $this->camposImportantes = [
            'estancia_id' => 'Estância Aduaneira',
            'porto_desembarque_id' => 'Porto de Desembarque',
            'localizacao_mercadoria_id' => 'Localização da Mercadoria',
            'TipoProcesso' => 'Regime Aduaneiro',
            'fob_total' => 'Valor FOB',
            'Pais_origem' => 'País de Origem',
        ];

        $this->camposNaoPreenchidos = collect($this->camposImportantes)
            ->filter(fn (string $label, string $campo): bool => blank($this->processo->{$campo}))
            ->all();
    }

    public function gerarTxt(GerarTxtProcessoAction $action): ?BinaryFileResponse
    {
        return $this->downloadFromAction(
            fn () => $action->execute(Auth::user(), $this->processo),
            'TXT gerado com sucesso.'
        );
    }

    public function gerarExtratoMercadoria(GerarExtratoMercadoriaProcessoAction $action): ?BinaryFileResponse
    {
        return $this->downloadFromAction(
            fn () => $action->execute(Auth::user(), $this->processo),
            'Extrato de mercadoria gerado com sucesso.'
        );
    }

    public function emitirNotaDespesa(EmitirNotaDespesaProcessoAction $action): ?BinaryFileResponse
    {
        return $this->downloadFromAction(
            fn () => $action->execute(Auth::user(), $this->processo),
            'Nota de despesa gerada com sucesso.'
        );
    }

    private function downloadFromAction(callable $callback, string $successMessage): ?BinaryFileResponse
    {
        try {
            $result = $callback();

            $this->dispatch('toast', type: 'success', message: $successMessage);

            return response()->download($result['path'], $result['filename'], [
                'Content-Type' => $result['mime'],
            ]);
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('Falha ao executar ação operacional do processo.', [
                'processo_id' => $this->processo->id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            $this->dispatch('toast',
                type: 'error',
                message: $e->getMessage() ?: 'Não foi possível executar a ação.'
            );

            return null;
        }
    }


    public function render()
    {
        return view('livewire.processo.processo-show');
    }
}
