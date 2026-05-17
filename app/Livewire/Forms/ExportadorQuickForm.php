<?php

namespace App\Livewire\Forms;

use Livewire\Component;
use App\Models\Exportador;
use App\Models\Pais;
use Illuminate\Support\Facades\Auth;

class ExportadorQuickForm extends Component
{
    public $showModal = false;
    
    public $ExportadorTaxID = '';
    public $Exportador = '';
    public $Pais = '';
    public $Endereco = '';
    public $Telefone = '';
    public $Email = '';
    
    protected $rules = [
        'ExportadorTaxID' => 'required|string|unique:exportadors,ExportadorTaxID',
        'Exportador' => 'required|string',
        'Pais' => 'required|exists:paises,id',
        'Endereco' => 'required|string',
        'Telefone' => 'required|string',
        'Email' => 'nullable|email',
    ];
    
    protected $listeners = ['abrirModalExportador' => 'open'];
    
    public function open()
    {
        $this->reset();
        $this->resetValidation();
        $this->showModal = true;
    }
    
    public function close()
    {
        $this->showModal = false;
    }
    
    public function save()
    {
        $this->validate();
        
        $empresa = Auth::user()->empresas->first();
        
        $exportador = Exportador::create([
            'ExportadorTaxID' => $this->ExportadorTaxID,
            'Exportador' => $this->Exportador,
            'Pais' => $this->Pais,
            'Endereco' => $this->Endereco,
            'Telefone' => $this->Telefone,
            'Email' => $this->Email,
            'empresa_id' => $empresa->id,
            'user_id' => Auth::id(),
        ]);
        
        $this->dispatch('exportadorCriado', exportadorId: $exportador->id, nome: $exportador->Exportador);
        
        $this->close();
        session()->flash('message', 'Exportador criado com sucesso!');
    }
    
    public function render()
    {
        return view('livewire.forms.exportador-quick-form', [
            'paises' => Pais::all(),
        ]);
    }
}

