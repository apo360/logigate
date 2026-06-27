<?php

namespace App\Livewire\Licenciamento;

use App\Application\Licenciamento\Actions\ConstituirProcessoAction;
use App\Application\Licenciamento\Actions\DuplicarLicenciamentoAction;
use App\Application\Licenciamento\Actions\GerarTxtLicenciamentoAction;
use App\Application\Licenciamento\Services\LicenciamentoOperationalReadinessService;
use App\Application\Licenciamento\Support\LicenciamentoFormSupport;
use App\Models\Licenciamento;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class LiicenciamentoShow extends Component
{
    use AuthorizesRequests;

    public Licenciamento $licenciamento;
    public bool $mostrarMercadorias = false;
    public array $checklist = [];
    public array $alertasOperacionais = [];
    public array $resumoFinanceiro = [];
    public array $timeline = [];
    public bool $prontoParaTxt = false;
    public bool $prontoParaProcesso = false;
    public array $motivosBloqueioTxt = [];
    public array $motivosBloqueioProcesso = [];
    public int $scoreProntidao = 0;

    public function mount(Licenciamento $licenciamento)
    {
        $this->authorize('view', $licenciamento);

        $this->licenciamento = $licenciamento
            ->load(app(LicenciamentoFormSupport::class)->relations())
            ->loadMissing([
                'procLicenFaturas.fatura.salesdoctotal',
                'procLicenFaturas.processo',
            ]);

        $this->carregarIndicadores();
    }

    public function abrirMercadorias(): void
    {
        $this->authorize('view', $this->licenciamento);

        $this->mostrarMercadorias = true;
        $this->dispatch('licenciamento-show-tab', tab: 'mercadorias');
    }

    public function fecharMercadorias(): void
    {
        $this->mostrarMercadorias = false;
    }

    public function gerarTxt(GerarTxtLicenciamentoAction $action)
    {
        $this->authorize('update', $this->licenciamento);
        $this->carregarIndicadores();

        if (! $this->prontoParaTxt) {
            $message = 'Licenciamento ainda não está pronto para gerar TXT.';
            session()->flash('error', $message);
            $this->dispatch('toast', type: 'warning', message: $message);

            return null;
        }

        try {
            $result = $action->execute($this->licenciamento);
            $this->licenciamento->refresh()->loadMissing([
                'mercadorias',
                'mercadoriasAgrupadas',
                'procLicenFaturas.processo',
                'procLicenFaturas.fatura.salesdoctotal',
            ]);
            $this->carregarIndicadores();

            return response()->streamDownload(function () use ($result) {
                echo $result['content'];
            }, $result['filename']);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('toast', type: 'error', message: $e->getMessage());

            return null;
        }
    }

    public function duplicarLicenciamento(DuplicarLicenciamentoAction $action)
    {
        $this->authorize('create', Licenciamento::class);

        try {
            $novo = $action->execute($this->licenciamento);
            session()->flash('success', 'Licenciamento duplicado com sucesso!');

            return redirect()->route('licenciamentos.show', $novo);
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao duplicar: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Erro ao duplicar: ' . $e->getMessage());

            return null;
        }
    }

    public function constituirProcesso(ConstituirProcessoAction $action)
    {
        $this->authorize('update', $this->licenciamento);
        $this->carregarIndicadores();

        if (! $this->prontoParaProcesso) {
            $message = 'Licenciamento ainda não está pronto para constituir processo.';
            session()->flash('error', $message);
            $this->dispatch('toast', type: 'warning', message: $message);

            return null;
        }

        try {
            $processo = $action->execute($this->licenciamento);
            session()->flash('success', 'Processo constituído com sucesso!');

            if (Route::has('processos.edit')) {
                return redirect()->route('processos.edit', $processo);
            }

            if (Route::has('processos.show')) {
                return redirect()->route('processos.show', $processo);
            }

            return null;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('toast', type: 'error', message: $e->getMessage());

            return null;
        }
    }

    public function validarLicenciamento(): void
    {
        $this->authorize('view', $this->licenciamento);

        $this->carregarIndicadores();
        $this->dispatch('toast', type: 'info', message: 'Validação operacional actualizada.');
    }

    private function carregarIndicadores(): void
    {
        $this->licenciamento->loadMissing([
            'cliente',
            'exportador',
            'estancia',
            'mercadorias',
            'mercadoriasAgrupadas',
            'procLicenFaturas.processo',
            'procLicenFaturas.fatura.salesdoctotal',
        ]);

        $analysis = app(LicenciamentoOperationalReadinessService::class)->analyze($this->licenciamento);

        $this->scoreProntidao = $analysis['score'];
        $this->checklist = $analysis['checklist'];
        $this->alertasOperacionais = $analysis['alerts'];
        $this->resumoFinanceiro = $analysis['financial_summary'];
        $this->timeline = $analysis['timeline'];
        $this->prontoParaTxt = $analysis['ready_for_txt'];
        $this->prontoParaProcesso = $analysis['ready_for_process'];
        $this->motivosBloqueioTxt = $analysis['txt_blockers'];
        $this->motivosBloqueioProcesso = $analysis['process_blockers'];
    }
    
    public function render()
    {
        return view('livewire.licenciamento.liicenciamento-show');
    }
}
