<?php

namespace App\Livewire\Forms;

use Livewire\Component;
use Livewire\WithFileUploads;

class ProcessoImportXml extends Component
{
    use WithFileUploads;
    public $file;

    protected $rules = [
        'file' => 'required|file|mimes:xml,txt',
    ];

    public function import()
    {
        $this->validate();
        // processar a importação do XML e emitir evento com dados mapeados
        // Implementação específica conforme XML que recebes.
        $this->dispatchBrowserEvent('toast', ['type'=>'success','message'=>'Ficheiro importado (provisório)']);
        $this->reset('file');
        $this->dispatchBrowserEvent('closeModal', ['id'=>'modalImport']);
    }

    public function render()
    {
        return view('livewire.forms.processo-import-xml');
    }
}
