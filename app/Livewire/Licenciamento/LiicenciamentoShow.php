<?php

namespace App\Livewire\Licenciamento;

use App\Application\Licenciamento\Actions\ConstituirProcessoAction;
use App\Application\Licenciamento\Actions\DuplicarLicenciamentoAction;
use App\Application\Licenciamento\Actions\GerarTxtLicenciamentoAction;
use App\Application\Licenciamento\Support\LicenciamentoFormSupport;
use App\Models\Licenciamento;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class LiicenciamentoShow extends Component
{
    use AuthorizesRequests;

    public Licenciamento $licenciamento;

    public function mount(Licenciamento $licenciamento)
    {
        $this->authorize('view', $licenciamento);

        $this->licenciamento = $licenciamento->load(app(LicenciamentoFormSupport::class)->relations());
    }

    // Implementação do código para gerar o TXT
    public function gerarTxt(GerarTxtLicenciamentoAction $action)
    {
        $this->authorize('generateTxt', $this->licenciamento);

        try {
            $result = $action->execute($this->licenciamento);
            
            // Forçar download
            return response()->streamDownload(function () use ($result) {
                echo $result['content'];
            }, $result['filename']);
            
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }

    // Implementação para duplicar o Licenciamento
    public function duplicar(DuplicarLicenciamentoAction $action)
    {
        $this->authorize('duplicate', $this->licenciamento);

        try {
            $novo = $action->execute($this->licenciamento);
            session()->flash('success', 'Licenciamento duplicado com sucesso!');
            return redirect()->route('licenciamentos.show', $novo);
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao duplicar: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    // Metódo para constituir o licenciamento em um processo aduaneiro
    public function constituirProcesso(ConstituirProcessoAction $action)
    {
        $this->authorize('constituteProcesso', $this->licenciamento);

        try {
            $processo = $action->execute($this->licenciamento);
            session()->flash('success', 'Processo constituído com sucesso!');
            return redirect()->route('processos.edit', $processo); // ou 'processos.show' conforme sua preferência
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }
    
    public function render()
    {
        return view('livewire.licenciamento.liicenciamento-show');
    }
}
