<?php

namespace App\Livewire\Forms;

use Livewire\Component;

class ProcessoResumo extends Component
{
    public $data;
    protected $listeners = ['refreshResumo' => '$refresh'];

    public function mount($data = [])
    {
        $this->data = $data;
    }

    public function render()
    {
        return view('livewire.forms.processo-resumo');
    }
}
