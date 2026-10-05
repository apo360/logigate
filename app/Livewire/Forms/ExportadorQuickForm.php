<?php

namespace App\Livewire\Forms;

use App\Domains\Exportadores\Actions\CreateOrAssociateExportadorAction;
use App\Domains\Exportadores\Data\ExportadorFormData;
use Livewire\Component;
use App\Models\Pais;
use Illuminate\Support\Facades\Auth;

class ExportadorQuickForm extends Component
{
    use \App\Livewire\Concerns\RequiresActiveEmpresa;

    public $showModal = false;
    
    public $ExportadorTaxID = '';
    public $Exportador = '';
    public $Pais = '';
    public $Endereco = '';
    public $Telefone = '';
    public $Email = '';
    
    protected function rules(): array
    {
        return array_intersect_key(\App\Domains\Exportadores\Services\ExportadorValidation::rules(), array_flip([
            'ExportadorTaxID', 'Exportador', 'Pais', 'Endereco', 'Telefone', 'Email',
        ]));
    }

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
        $this->authorize('create', \App\Models\Exportador::class);
        $data = $this->validate();
        
        $empresa = \App\Support\TenantContext::empresa();
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
