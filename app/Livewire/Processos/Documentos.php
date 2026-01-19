<?php

namespace App\Livewire\Processos;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Processo;
use App\Models\ProcessoDocumento;

class Documentos extends Component
{
    use WithFileUploads;

    public Processo $processo;
    public array $files = [];

    protected $rules = [
        'files.*' => 'file|max:5120|mimes:pdf,jpg,png,doc,docx,xls,xlsx',
    ];

    public function save()
    {
        $this->validate();

        foreach ($this->files as $file) {

            $path = $file->store('processos/documentos', 'public');

            ProcessoDocumento::create([
                'processo_id'    => $this->processo->id,
                'nome_original'  => $file->getClientOriginalName(),
                'ficheiro'       => $path,
                'tipo'           => $file->getClientOriginalExtension(),
                'tamanho'        => $file->getSize(),
            ]);
        }

        $this->reset('files');

        $this->dispatch('toast',
            type: 'success',
            message: 'Documentos carregados com sucesso!'
        );
    }

    public function remove($id)
    {
        ProcessoDocumento::findOrFail($id)->delete();

        $this->dispatch('toast',
            type: 'success',
            message: 'Documento removido!'
        );
    }

    public function render()
    {
        return view('livewire.processos.documentos', [
            'docs' => $this->processo->documentos
        ]);
    }
}

