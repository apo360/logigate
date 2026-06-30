<?php

namespace App\Livewire\Processo;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Processo;
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
            'files' => 'Use a aba Documentos integrada ao Arquivo central para anexar documentos privados ao processo.',
        ]);
    }

    public function remove($id)
    {
        throw ValidationException::withMessages([
            'documento' => 'Use a aba Documentos integrada ao Arquivo central para remover anexos do processo.',
        ]);
    }

    public function render()
    {
        return view('livewire.processo.documentos', [
            'docs' => $this->processo->documentos
        ]);
    }
}
