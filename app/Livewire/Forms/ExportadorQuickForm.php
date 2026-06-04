<?php

namespace App\Livewire\Forms;

use App\Domains\Exportadores\Actions\CreateOrAssociateExportadorAction;
use App\Domains\Exportadores\Data\ExportadorFormData;
use Livewire\Component;
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
        'ExportadorTaxID' => 'nullable|string|min:6|max:20',
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
        $data = $this->validate();
        
        $empresa = Auth::user()->empresas->first();
        $action = app(CreateOrAssociateExportadorAction::class);
        
        $exportador = $action->execute(
            ExportadorFormData::fromArray($data),
            $empresa,
            Auth::user()
        );
        
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
