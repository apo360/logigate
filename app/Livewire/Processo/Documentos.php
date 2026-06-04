<?php

namespace App\Livewire\Processo;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use Illuminate\Validation\ValidationException;

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

        throw ValidationException::withMessages([
            'files' => 'Upload de documentos do processo está temporariamente bloqueado até migração para S3 privado com autorização por documento.',
        ]);

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
        throw ValidationException::withMessages([
            'documento' => 'Remoção de documentos do processo está temporariamente bloqueada até migração para S3 privado com autorização por documento.',
        ]);

        ProcessoDocumento::findOrFail($id)->delete();

        $this->dispatch('toast',
            type: 'success',
            message: 'Documento removido!'
        );
    }

    public function render()
    {
        return view('livewire.processo.documentos', [
            'docs' => $this->processo->documentos
        ]);
    }
}
