<?php

namespace App\Livewire\Licenciamento;

use App\Application\Licenciamento\Actions\ConstituirProcessoAction;
use App\Application\Licenciamento\Actions\DuplicarLicenciamentoAction;
use App\Application\Licenciamento\Actions\GerarTxtLicenciamentoAction;
use App\Models\Licenciamento;
use Livewire\Component;

class LiicenciamentoShow extends Component
{
    public Licenciamento $licenciamento;

    public function mount(Licenciamento $licenciamento)
    {
        $this->licenciamento = $licenciamento->load([
            'cliente', 'exportador', 'estancia', 'mercadorias', 
            'documentosArquivos', 'mercadoriasAgrupadas'
        ]);
    }

    // Implementação do código para gerar o TXT
    public function gerarTxt(GerarTxtLicenciamentoAction $action)
    {
        try {
            $result = $action->execute($this->licenciamento);
            
            // Forçar download
            return response()->streamDownload(function () use ($result) {
                echo $result['content'];
            }, $result['filename']);
            
        } catch (\Exception $e) {
            dd($e->getMessage()); // mostra o erro imediatamente
            session()->flash('error', $e->getMessage());
            return redirect()->back();
        }
    }

    // Implementação para duplicar o Licenciamento
    public function duplicar(DuplicarLicenciamentoAction $action)
    {
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
