<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PautaAduaneiraImport;

class ImportPautaAduaneira extends Component
{
    use WithFileUploads;

    public $file;

    protected $rules = [
        'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Limite de 10MB
    ];

    public function import()
    {
        ini_set('max_execution_time', 300); // Aumenta para 5 minutos

        $this->validate();

        Excel::import(new PautaAduaneiraImport, $this->file->path());

        session()->flash('success', 'Pauta aduaneira importada com sucesso!');
    }

    public function render()
    {
        return view('livewire.import-pauta-aduaneira');
    }
}
